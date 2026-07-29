# Guia Completo de Implantação Livre — NovoSGA (Open-Source Edition)

> **Este repositório contém extensões livres, abertas e higienizadas do NovoSGA (originalmente criado por Rogério Lino), licenciadas sob GPL v3 / MIT.**  
> O objetivo é disponibilizar para órgãos públicos, prefeituras e desenvolvedores uma suite moderna de autoatendimento com sintetizador de voz (TTS) nativo em português, teclado touch e guichê resiliênte.

---

## 1. O Que Há de Novo Neste Fork?

### 1.1 Bundles Customizados Symfony (`/src/`)
1. **`NovosgaChamadaSenhaBundle` (Guichê de Atendimento Avançado v1.5)**
   - Rota: `/novosga.chamadasenha/`
   - **Aviso Sonoro Suave**: Alerta com bipe agradável na tela do atendente sempre que um novo usuário retira senha no totem (quando o atendente está ocioso).
   - **Toast Visual Flutuante**: Notificação animada no topo direito indicando que há novas pessoas na fila.
   - **Resiliência Anti-Queda**: Eliminação de timeouts do *polling*, reconexão automática e limpeza de scripts redundantes.
2. **`NovosgaMidiaAdminBundle` (Painel Central de Mídias e Som v1.2)**
   - Rota: `/novosga.midiaadmin/`
   - **Gestão de Temas (`theme.json`)**: Configuração de logotipos da unidade, títulos e subtítulos personalizados.
   - **Controle de Campainha**: Upload de arquivos de áudio `.mp3/.wav` e seleção de toques modernos (`chime-1`, `chime-2`).
   - **Teste Sonoro e Comando Remoto**: Disparo remoto de teste de áudio nas TVs da sala de espera.
3. **`NovosgaGestorUnidadeBundle` (Painel do Gestor Local v1.1)**
   - Rota: `/novosga.gestorunidade/`
   - Gestão descentralizada de atendentes, permissões e serviços da unidade local.

### 1.2 SPAs Modernos em React (`/projects/`)
1. **`painel-tv` (Kiosk TV com Sintetizador de Voz TTS e Mídia)**
   - **Web Speech API nativa**: Sintetizador de voz em português com mapeamento fonético inteligente (números, letras e nomes de guichê) e seleção de gênero de voz (Feminino/Masculino/Neural).
   - **Autoplay Zero-Click**: Suporte a quiosques TV autônomos e notificação flutuante para ativação sonora em navegadores com restrição de Autoplay.
   - **Guarda Anti-Silêncio (`isFirstFetchRef`)**: Garantia de que a primeira chamada do dia é anunciada em voz e áudio desde o segundo zero.
2. **`totem-react` (Totem de Autoatendimento Touchscreen)**
   - **Teclado Virtual Otimizado**: Interface ergonômica para telas de toque em totems industriais.
   - **Validação Algorítmica de CPF**: Verificação imediata do dígito verificador com feedback visual (Toast) em tempo real.

---

## 2. Requisitos de Infraestrutura
- **Sistema Operacional**: Linux (Ubuntu 22.04 / 24.04 LTS ou Debian 12 recomendado).
- **Servidor Web**: Nginx 1.24+ ou Apache 2.4 com PHP-FPM.
- **PHP**: Versão **8.3** (compatível com 8.2+) com extensões: `php-pgsql` (ou `php-mysql`), `php-xml`, `php-mbstring`, `php-curl`, `php-intl`, `php-zip`, `php-gd`.
- **Banco de Dados**: PostgreSQL 14+ ou MySQL 8.0+.
- **Node.js**: Versão **20+ LTS** (para build dos SPAs React em `/projects/`).
- **Composer**: Versão 2.6+.

---

## 3. Passo a Passo de Instalação e Compilação

### 3.1 Instalação do Core Symfony
1. Clone este repositório no seu servidor de aplicação (ex: `/opt/novosga` ou `/var/www/novosga`):
   ```bash
   git clone https://github.com/KduBroseguinOfc/novosga.git /opt/novosga
   cd /opt/novosga
   ```
2. Instale as dependências PHP em modo de produção:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

### 3.2 Registro dos Bundles Customizados (`config/bundles.php`)
Certifique-se de que o arquivo `config/bundles.php` possui os nossos Bundles registrados:
```php
return [
    // ... bundles padrão do Symfony e NovoSGA ...
    Novosga\ChamadaSenhaBundle\NovosgaChamadaSenhaBundle::class => ['all' => true],
    Novosga\MidiaAdminBundle\NovosgaMidiaAdminBundle::class => ['all' => true],
    Novosga\GestorUnidadeBundle\NovosgaGestorUnidadeBundle::class => ['all' => true],
];
```

### 3.3 Compilação dos SPAs React (Painel TV e Totem)
Os SPAs residem no diretório `/projects/` e geram seus pacotes estáticos para dentro do diretório `/public/` do Symfony:

#### A. Compilar o Painel TV (`/projects/painel-tv/`)
```bash
cd /opt/novosga/projects/painel-tv
npm install
npm run build
```
> O comando `npm run build` irá transpilar a aplicação com Vite e gerar os estáticos em `/opt/novosga/public/painel/`.

#### B. Compilar o Totem Touch (`/projects/totem-react/`)
```bash
cd /opt/novosga/projects/totem-react
npm install
npm run build
```
> O build será publicado em `/opt/novosga/public/totem/`.

---

## 4. Guia de Chaves de Segurança e OAuth2 ("Zero-Secret")

O NovoSGA utiliza autenticação OAuth2 (via chaves assimétricas RSA) para emissão de tokens JWT para os SPAs e terminais. **Nunca utilize chaves copiadas de outros servidores ou versionadas no Git.**

### 4.1 Gerando Chaves RSA do OAuth2 (2048 bits)
Crie um diretório seguro (ex: `var/keys/` ou `config/jwt/`) e execute:
```bash
# 1. Gerar Chave Privada RSA
openssl genrsa -out var/keys/private.key 2048

# 2. Gerar Chave Pública correspondente
openssl rsa -in var/keys/private.key -pubout -out var/keys/public.key

# 3. Ajustar permissões de segurança estritas (somente o usuário web lê)
chmod 600 var/keys/private.key
chmod 644 var/keys/public.key
chown -R www-data:www-data var/keys/
```

### 4.2 Configurando o Banco de Dados e Parâmetros (`.env.local`)
Crie o arquivo `.env.local` na raiz `/opt/novosga/` baseado no `.env`:
```ini
APP_ENV=prod
APP_SECRET=sua_chave_secreta_aletoria_de_32_caracteres
DATABASE_URL="postgresql://usuario:senha@127.0.0.1:5432/novosga?serverVersion=15&charset=utf8"
OAUTH_PRIVATE_KEY="var/keys/private.key"
OAUTH_PUBLIC_KEY="var/keys/public.key"
```

---

## 5. Permissões de Cache e Arquivos Linux (`chmod / chown`)

Para que o Symfony e o PHP-FPM operem sem erros `HTTP 500` ao gravar cache ou sessões:
```bash
# Definir proprietário para o usuário do servidor web (ex: www-data ou novosga:www-data)
sudo chown -R www-data:www-data /opt/novosga/var/
sudo chmod -R 775 /opt/novosga/var/

# Permissão para o diretório de mídias e temas
sudo mkdir -p /mnt/media/config
sudo chown -R www-data:www-data /mnt/media/
sudo chmod -R 775 /mnt/media/
```
> **Atenção**: Sempre que executar `bin/console cache:clear` via terminal, rode o comando de `chown/chmod` logo em seguida para evitar que a pasta `var/cache/` fique pertencendo a `root`.

---

## 6. Configuração de Vozes e Áudio TTS na TV
O módulo `painel-tv` usa a **Web Speech API** nativa do navegador. Para garantir voz clara em português:
- **No Linux (Raspberry Pi / Kiosks)**: Instale o motor fonético `speech-dispatcher` ou `espeak-ng`, ou utilize o **Chromium** em modo Kiosk com flags de autoplay:
  ```bash
  chromium-browser --kiosk --autoplay-policy=no-user-gesture-required https://seu-servidor/painel/
  ```
- **No Windows / Android**: O Edge/Chrome utiliza automaticamente as vozes neurais de altíssima qualidade do sistema (ex: *Microsoft Francisca / Antonio* ou *Google Português do Brasil*).

---

## 7. Como Contribuir
Contribuições são bem-vindas! Ao submeter Pull Requests:
1. Certifique-se de que nenhuma informação privada (IPs, segredos, senhas ou domínios corporativos) seja enviada.
2. Mantenha compatibilidade com o padrão Symfony 6/7 e PHP 8.3.
3. Respeite as diretrizes das licenças GPL v3 / MIT.
