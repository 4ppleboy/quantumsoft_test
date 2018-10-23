Init project

docker-compose up -d
composer install
su -c "sed -i '/[0-9\.] task.local/d' /etc/hosts && echo $(docker inspect -f "{{.NetworkSettings.Networks.task_default.Gateway}}" task_nginx_1) task.local >> /etc/hosts"
