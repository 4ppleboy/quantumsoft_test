![preview](https://i.ibb.co/sWQ08Gh/2018-12-11-13-23-27.png)



Init project

* `docker-compose up -d`
* `docker exec -i task_php_1 composer install`
* `docker exec -i task_php_1 chmod 777 app/state`
* ```su -c "sed -i '/[0-9\.] task.local/d' /etc/hosts && echo $(docker inspect -f "{{.NetworkSettings.Networks.task_default.Gateway}}" task_nginx_1) task.local >> /etc/hosts"```
