#!/bin/bash
if [ -x "$(command -v docker)" ]; then
    echo "Docker is installed on your system. That's nice!!!"
    #create config directory
    mkdir -p ./docroot/config/sync
    chmod 755 ./docroot/config/sync
    #cp ./docroot/web/sites/default/default.settings.php ./docroot/web/sites/default/settings.php
    chmod 755 ./docroot/web/sites/default/settings.php
    mkdir ./docroot/web/sites/default/files
    chmod 777 ./docroot/web/sites/default/files
    cd docker
    docker-compose up -d
    echo "Creating the Solr Core: xnavi"
    docker exec -it docker_xnavi_d9_solr_1 ./bin/solr create_core -c xnavi -d /opt/solr/solr_xnavi_config -n xnavi_core
else
    echo "Docker is not installed. Please install docker and docker-compose"
    read -p "I would install docker and docker-compose for you. Do you whish to continue [y/n]?" -n 1 -r
    echo
    if [[ $REPLY =~ [Yy]$ ]]
    then
        apt install apt-transport-https ca-certificates curl software-properties-common
        curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo apt-key add -
        add-apt-repository "deb [arch=amd64] https://download.docker.com/linux/ubuntu focal stable"
        apt update
        apt-cache policy docker-ce
        apt install docker-ce
        systemctl status docker
        #Install docker-compose
        echo "Now I will install docker-compose"
        curl -L https://github.com/docker/compose/releases/download/1.29.2/docker-compose-`uname -s`-`uname -m` -o /usr/local/bin/docker-compose
        chmod +x /usr/local/bin/docker-compose
        docker-compose --version
        echo "If everything has worked fine you should have now Docker and Docker Composed installed on your system."
        echo "To install the x.Navi-Framework please start the install.sh script with sudo permission."
    fi
fi
