# script para instalacao automatica
# sistema SGA
# versao 1.0
# Eduardo Policarpo em 28/06/2023

#!/bin/bash

# Função para instalar o Docker
install_docker() {
  sudo apt update -y || { echo "Erro ao atualizar os pacotes."; exit 1; }
  sudo apt install -y apt-transport-https ca-certificates curl software-properties-common
  curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg
  echo "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
  sudo apt update -y
  apt-cache policy docker-ce
  sudo apt install -y docker-ce
}

# Função para instalar o Docker Compose
install_docker_compose() {
  sudo curl -L "https://github.com/docker/compose/releases/download/$(curl -s https://api.github.com/repos/docker/compose/releases/latest |
    grep 'tag_name' | cut -d '"' -f 4)/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
  sudo chmod +x /usr/local/bin/docker-compose
}

# Verificar se o Docker está instalado
if ! command -v docker >/dev/null 2>&1; then
  echo -e "\033[1;32m"'INSTALANDO DOCKER ...'"\e[0m"
  install_docker
fi

# Verificar se o Docker Compose está instalado
if ! command -v docker-compose >/dev/null 2>&1; then
  echo -e "\033[1;32m"'INSTALANDO DOCKER COMPOSE ...'"\e[0m"
  install_docker_compose
fi

# Função genérica para perguntas interativas
ask_question() {
  local question="$1"
  local var_name="$2"
  local input

  while true; do
    read -p "$(tput setaf 3)$question$(tput sgr0)" input
    if [[ -z $input ]]; then
      echo -e "$(tput setaf 1)É obrigatório preencher este campo.$(tput sgr0)"
    else
      eval "$var_name=\"$input\""
      break
    fi
  done
}

# Nome de usuário do administrador
ask_question "Defina o nome de usuário do administrador: " NOVOSGA_ADMIN_USERNAME

# Senha do administrador
ask_question "Defina a senha do administrador (mínimo 6 caracteres): " NOVOSGA_ADMIN_PASSWORD

# Senha do MySQL
ask_question "Defina a senha do banco de dados MySQL: " MYSQL_PASSWORD

# Primeiro nome do administrador
ask_question "Defina o primeiro nome do administrador: " NOVOSGA_ADMIN_FIRSTNAME

# Sobrenome do administrador
ask_question "Defina o sobrenome do administrador: " NOVOSGA_ADMIN_LASTNAME

# Nome da unidade
ask_question "Defina o nome da unidade: " NOVOSGA_UNITY_NAME

# Código da unidade
ask_question "Defina o código da unidade: " NOVOSGA_UNITY_CODE

# Nome da prioridade normal
ask_question "Defina o nome da prioridade normal: " NOVOSGA_NOPRIORITY_NAME

# Descrição da prioridade normal
ask_question "Defina a descrição da prioridade normal: " NOVOSGA_NOPRIORITY_DESCRIPTION

# Nome da prioridade
ask_question "Defina o nome da prioridade: " NOVOSGA_PRIORITY_NAME

# Descrição da prioridade
ask_question "Defina a descrição da prioridade: " NOVOSGA_PRIORITY_DESCRIPTION

# Nome do local
ask_question "Defina o nome do local: " NOVOSGA_PLACE_NAME

# Criação dinâmica do conteúdo do docker-compose.yml em uma variável
docker_compose=$(cat <<EOF
version: '2'

services:
  novosga:
    image: novosga/novosga:latest
    restart: always
    depends_on:
      - mysqldb
    ports:
      - "80:80"
      - "2020:2020"
    environment:
      APP_ENV: 'prod'
      DATABASE_URL: 'mysql://novosga:${MYSQL_PASSWORD}@mysqldb:3306/novosga2?charset=utf8mb4&serverVersion=5.7'
      NOVOSGA_ADMIN_USERNAME: '${NOVOSGA_ADMIN_USERNAME}'
      NOVOSGA_ADMIN_PASSWORD: '${NOVOSGA_ADMIN_PASSWORD}'
      NOVOSGA_ADMIN_FIRSTNAME: '${NOVOSGA_ADMIN_FIRSTNAME}'
      NOVOSGA_ADMIN_LASTNAME: '${NOVOSGA_ADMIN_LASTNAME}'
      NOVOSGA_UNITY_NAME: '${NOVOSGA_UNITY_NAME}'
      NOVOSGA_UNITY_CODE: '${NOVOSGA_UNITY_CODE}'
      NOVOSGA_NOPRIORITY_NAME: '${NOVOSGA_NOPRIORITY_NAME}'
      NOVOSGA_NOPRIORITY_DESCRIPTION: '${NOVOSGA_NOPRIORITY_DESCRIPTION}'
      NOVOSGA_PRIORITY_NAME: '${NOVOSGA_PRIORITY_NAME}'
      NOVOSGA_PRIORITY_DESCRIPTION: '${NOVOSGA_PRIORITY_DESCRIPTION}'
      NOVOSGA_PLACE_NAME: '${NOVOSGA_PLACE_NAME}'
      TZ: 'America/Sao_Paulo'
      LANGUAGE: 'pt_BR'
  mysqldb:
    image: mysql:5.7
    restart: always
    environment:
      MYSQL_USER: 'novosga'
      MYSQL_DATABASE: 'novosga2'
      MYSQL_ROOT_PASSWORD: '${MYSQL_PASSWORD}'
      MYSQL_PASSWORD: '${MYSQL_PASSWORD}'
      TZ: 'America/Sao_Paulo'
EOF
)

# Salvar o conteúdo no arquivo docker-compose.yml
echo "$docker_compose" > docker-compose.yml

# Executar docker-compose up -d e cria o container do sistema
docker-compose up -d
