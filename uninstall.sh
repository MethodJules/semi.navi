#/bin/bash
cd docker
docker-compose down
cd ..
rm -rf database
rm -rf docroot/web/sites/default/files/
rm -rf docroot/config/sync
#rm -rf docroot/web/sites/default/settings.php 
docker volume rm docker_xnavi_d9_core