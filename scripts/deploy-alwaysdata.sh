#!/usr/bin/env bash
set -euo pipefail

: "${ALWAYSDATA_HOST:?Set the Alwaysdata SSH host in the production environment}"
: "${ALWAYSDATA_USER:?Set the Alwaysdata SSH user in the production environment}"
: "${ALWAYSDATA_DEPLOY_PATH:?Set the deployment path in the production environment}"
: "${ALWAYSDATA_KNOWN_HOSTS:?Set a verified SSH host key in the production environment}"
: "${ALWAYSDATA_SSH_KEY:?Set the SSH private key as an environment secret}"
: "${GITHUB_SHA:?The release must have a GitHub commit SHA}"

if [[ ! "$ALWAYSDATA_HOST" =~ ^[A-Za-z0-9.-]+$ ]]; then
    echo "ALWAYSDATA_HOST contains invalid characters." >&2
    exit 1
fi

if [[ ! "$ALWAYSDATA_USER" =~ ^[A-Za-z0-9._-]+$ ]]; then
    echo "ALWAYSDATA_USER contains invalid characters." >&2
    exit 1
fi

if [[ ! "$ALWAYSDATA_DEPLOY_PATH" =~ ^/home/[A-Za-z0-9._/-]+$ || "$ALWAYSDATA_DEPLOY_PATH" == *..* ]]; then
    echo "ALWAYSDATA_DEPLOY_PATH must be a safe absolute path under /home." >&2
    exit 1
fi

if [[ ! "$GITHUB_SHA" =~ ^[a-f0-9]{40}$ ]]; then
    echo "GITHUB_SHA is not a valid commit SHA." >&2
    exit 1
fi

ssh_dir="$HOME/.ssh"
mkdir -p "$ssh_dir"
chmod 700 "$ssh_dir"
printf '%s\n' "$ALWAYSDATA_SSH_KEY" > "$ssh_dir/id_ed25519"
printf '%s\n' "$ALWAYSDATA_KNOWN_HOSTS" > "$ssh_dir/known_hosts"
chmod 600 "$ssh_dir/id_ed25519" "$ssh_dir/known_hosts"
ssh-keygen -y -f "$ssh_dir/id_ed25519" > /dev/null

ssh_options=(-i "$ssh_dir/id_ed25519" -o IdentitiesOnly=yes -o BatchMode=yes -o StrictHostKeyChecking=yes)
target="${ALWAYSDATA_USER}@${ALWAYSDATA_HOST}"
release_path="${ALWAYSDATA_DEPLOY_PATH}/releases/${GITHUB_SHA}"
archive_path="${ALWAYSDATA_DEPLOY_PATH}/releases/${GITHUB_SHA}.tar.gz"
local_archive="$RUNNER_TEMP/quizz-${GITHUB_SHA}.tar.gz"

ssh "${ssh_options[@]}" "$target" \
    "mkdir -p '${ALWAYSDATA_DEPLOY_PATH}/releases' '${ALWAYSDATA_DEPLOY_PATH}/shared' && test -f '${ALWAYSDATA_DEPLOY_PATH}/shared/.env.local' && php -r 'exit(PHP_VERSION_ID >= 80300 ? 0 : 1);'"

tar \
    --exclude='./.git' \
    --exclude='./.github' \
    --exclude='./.env' \
    --exclude='./.env.*' \
    --exclude='./.env.save' \
    --exclude='./var' \
    --exclude='./tests' \
    --exclude='./phpunit.xml.dist' \
    --exclude='./.phpunit.result.cache' \
    --exclude='./docker-compose.yml' \
    --exclude='./docker-compose.override.yml' \
    --exclude='./Dockerfile' \
    --exclude='./.dockerignore' \
    --exclude='./wamp-vhost.conf.example' \
    --exclude='./scripts/deploy-alwaysdata.sh' \
    -czf "$local_archive" .

scp "${ssh_options[@]}" "$local_archive" "${target}:${archive_path}"

ssh "${ssh_options[@]}" "$target" "bash -s -- '${ALWAYSDATA_DEPLOY_PATH}' '${GITHUB_SHA}'" <<'REMOTE'
set -euo pipefail

base_path="$1"
release_id="$2"
release_path="$base_path/releases/$release_id"
shared_path="$base_path/shared"
archive_path="$base_path/releases/$release_id.tar.gz"

mkdir -p "$release_path"
tar -xzf "$archive_path" -C "$release_path"
rm "$archive_path"

printf 'APP_ENV=prod\nAPP_DEBUG=0\n' > "$release_path/.env"
ln -s "$shared_path/.env.local" "$release_path/.env.local"
mkdir -p "$shared_path/var/log" "$release_path/var/cache"
ln -s "$shared_path/var/log" "$release_path/var/log"

for directory in profil type reponse reponse_bonus; do
    if [ -d "$release_path/public/$directory" ] && [ ! -e "$shared_path/public/$directory" ]; then
        mkdir -p "$shared_path/public"
        cp -a "$release_path/public/$directory" "$shared_path/public/$directory"
    fi

    mkdir -p "$shared_path/public/$directory"
    rm -rf "$release_path/public/$directory"
    ln -s "$shared_path/public/$directory" "$release_path/public/$directory"
done

cd "$release_path"
php bin/console doctrine:migrations:migrate --no-interaction --env=prod
php bin/console cache:clear --env=prod

ln -s "$release_path" "$base_path/current-$release_id"
mv -Tf "$base_path/current-$release_id" "$base_path/current"
REMOTE

rm -f "$local_archive"
echo "Deployed release $GITHUB_SHA to Alwaysdata."
