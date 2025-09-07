# 개발 편의를 위해 admin과 www-data 모두 접근 가능하도록
sudo chown -R admin:www-data /home/admin/webapps/ekge/src
sudo chmod -R 775 /home/admin/webapps/ekge/src

# Laravel 핵심 디렉토리는 www-data가 완전 제어
sudo chown -R www-data:www-data /home/admin/webapps/ekge/src/storage
sudo chown -R www-data:www-data /home/admin/webapps/ekge/src/bootstrap/cache
sudo chmod -R 777 /home/admin/webapps/ekge/src/storage
sudo chmod -R 777 /home/admin/webapps/ekge/src/bootstrap/cache