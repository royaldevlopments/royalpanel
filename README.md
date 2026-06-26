<div align="center">
  <img src="https://github.com/user-attachments/assets/a1a6df48-7925-43c9-81d6-e2351e6c6bb8" alt="Royal Panel" width="120"/>

  # Royal Panel

  <p><strong>A modern, feature-rich game server management panel — built on Pterodactyl.</strong></p>

  <p>
    <a href="https://papa.codenestsolution.in">🔗 Live Demo</a>
  </p>

  <br>

  <img src="https://img.shields.io/badge/PHP-8.3-777BB4?style=flat&logo=php&logoColor=white" />
  <img src="https://img.shields.io/badge/Node-20-339933?style=flat&logo=nodedotjs&logoColor=white" />
  <img src="https://img.shields.io/badge/License-MIT-yellow" />
</div>

---

## ✨ Features

- **Custom Login Page** — Two-panel layout with particle effects, editable hero text, and social OAuth (Discord/GitHub/Google)
- **Neon Gaming Theme** — Unique dark theme with custom CSS
- **Step Wizard Admin Pages** — Create Users/Servers/Nodes with a clean step-by-step wizard
- **In-Browser SFTP Client** — File manager right in the panel, no separate SFTP client needed
- **Direct Upload** — Upload files up to 20GB directly through the panel
- **Discord Bot Integration** — 22+ slash commands, server management, user lookup, and 2FA via Discord
- **Discord 2FA** — Two-factor authentication through Discord DMs alongside TOTP
- **OAuth Login** — Sign in with Discord, GitHub, or Google
- **Admin-Editable Email Templates** — Customize every email sent by the panel from the admin UI
- **Registration Toggle** — Enable/disable public registration
- **Discord Link System** — Link Discord accounts to panel accounts with enforced linking option
- **Blueprint Extension Support** — Run Pterodactyl extensions via Blueprint
- **mcplugins** — Minecraft plugin browser with 14K+ plugins from Modrinth

## 🚀 Tech Stack

| Component | Technology |
|-----------|-----------|
| Backend | PHP 8.3, Laravel |
| Frontend | React, TypeScript, Webpack |
| Database | MariaDB / MySQL |
| Game Daemon | Wings (Docker) |
| Bot | Discord.js v14 |
| Proxy | Nginx |

## 🛠️ Quick Start

```bash
# Clone the repository
git clone https://github.com/royaldevlopments/royalpanel.git
cd royalpanel

# Install dependencies
composer install --no-dev
npm install --production

# Configure environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate --force

# Build frontend
npm run build

# Set permissions
chown -R www-data:www-data .
```

## 🤖 Discord Bot

Royal Panel includes a fully integrated Discord bot:

```bash
# Set Discord Bot Token in Admin → Royal → Advanced
systemctl restart royalpanel-bot
```

Features: `/link`, `/unlink`, `/status`, `/my-servers`, `/server-info`, `/server-power`, `/server-command`, `/admin-stats`, and full admin CRUD commands for users & servers.

## 📧 Email Templates

Customize all email notifications from the admin panel at **Admin → Royal → Email Templates** — no Blade file editing required.

## 👨‍💻 About

**Royal Panel** is developed and maintained by **Shaurya** at **Royal Devlopments**.

> Built with ❤️ for the game hosting community.

---

<div align="center">
  <sub>© 2026 Royal Devlopments. All rights reserved.</sub>
</div>
