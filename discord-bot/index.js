const { Client, GatewayIntentBits, REST, Routes, EmbedBuilder } = require('discord.js');
const axios = require('axios');

const PANEL_URL = process.env.PANEL_URL || 'https://papa.codenestsolution.in';
const PANEL_API_TOKEN = process.env.PANEL_API_TOKEN || '';

if (!PANEL_API_TOKEN) {
    console.error('PANEL_API_TOKEN environment variable is required');
    process.exit(1);
}

let discordBotToken = process.env.DISCORD_BOT_TOKEN || '';
let client = null;
let guildId = '';
let adminRoleId = '';

function getApiClient() {
    return axios.create({
        baseURL: PANEL_URL,
        headers: {
            'X-Bot-Token': PANEL_API_TOKEN,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        timeout: 10000,
    });
}

async function fetchConfig() {
    try {
        const res = await getApiClient().get('/api/client/bot/config');
        guildId = res.data.guild_id || '';
        adminRoleId = res.data.admin_role_id || '';
        const configBotToken = res.data.bot_token || '';
        if (configBotToken && !discordBotToken) {
            discordBotToken = configBotToken;
        }
        console.log(`Config fetched: guild=${guildId}, adminRole=${adminRoleId}, hasBotToken=${!!discordBotToken}`);
        return true;
    } catch (err) {
        console.error('Failed to fetch config:', err.message);
        return false;
    }
}

async function poll2FACodes() {
    if (!client || !client.isReady()) return;
    try {
        const api = getApiClient();
        const res = await api.get('/api/client/bot/2fa/pending');
        const codes = res.data;

        for (const code of codes) {
            try {
                const user = await client.users.fetch(code.discord_id);
                if (user) {
                    const embed = new EmbedBuilder()
                        .setTitle('🔐 Discord 2FA Code')
                        .setDescription(`Your 2FA code is: **${code.code}**`)
                        .setColor(0x00FF00)
                        .setFooter({ text: 'This code expires in 3 minutes' });

                    await user.send({ embeds: [embed] });
                    console.log(`Sent 2FA code to ${code.discord_id}`);

                    await api.post('/api/client/bot/2fa/mark-sent', { id: code.id });
                }
            } catch (dmErr) {
                console.error(`Failed to DM user ${code.discord_id}:`, dmErr.message);
            }
        }
    } catch (err) {
        console.error('Poll 2FA codes error:', err.message);
    }
}

async function registerCommands() {
    if (!client || !client.user) return;
    const commands = [
        {
            name: 'link',
            description: 'Link your Discord account to the panel',
            options: [
                {
                    name: 'code',
                    type: 3,
                    description: 'The link code from your account page',
                    required: true,
                },
            ],
        },
        {
            name: 'unlink',
            description: 'Unlink your Discord account from the panel',
        },
        {
            name: '2fa',
            description: 'Toggle Discord 2FA for your account',
            options: [
                {
                    name: 'action',
                    type: 3,
                    description: 'enable or disable',
                    required: true,
                    choices: [
                        { name: 'Enable', value: 'enable' },
                        { name: 'Disable', value: 'disable' },
                    ],
                },
            ],
        },
    ];

    try {
        const rest = new REST({ version: '10' }).setToken(discordBotToken);
        await rest.put(Routes.applicationCommands(client.user.id), { body: commands });
        console.log('Slash commands registered');
    } catch (err) {
        console.error('Failed to register commands:', err.message);
    }
}

async function startBot() {
    const configOk = await fetchConfig();
    if (!configOk) {
        console.log('Will retry config fetch in 10 seconds...');
        setTimeout(startBot, 10000);
        return;
    }

    if (!discordBotToken) {
        console.log('Discord Bot Token not configured. Set it in Admin → Royal → Advanced and restart the service.');
        console.log('Will retry in 30 seconds...');
        setTimeout(startBot, 30000);
        return;
    }

    client = new Client({
        intents: [
            GatewayIntentBits.Guilds,
            GatewayIntentBits.GuildMessages,
            GatewayIntentBits.DirectMessages,
            GatewayIntentBits.MessageContent,
        ],
    });

    client.once('ready', async () => {
        console.log(`Logged in as ${client.user.tag}`);
        await registerCommands();

        setInterval(() => {
            poll2FACodes();
        }, 3000);
    });

    client.on('interactionCreate', async (interaction) => {
        if (!interaction.isCommand()) return;

        const { commandName, options, user } = interaction;

        if (commandName === 'link') {
            const code = options.getString('code');
            await interaction.deferReply({ ephemeral: true });

            try {
                const api = getApiClient();
                const res = await api.post('/api/client/bot/link/verify', {
                    discord_id: user.id,
                    code: code,
                });

                if (res.data.success) {
                    const embed = new EmbedBuilder()
                        .setTitle('✅ Account Linked')
                        .setDescription('Your Discord account has been successfully linked to the panel!')
                        .setColor(0x00FF00);
                    await interaction.editReply({ embeds: [embed] });
                } else {
                    await interaction.editReply({ content: '❌ Failed to link. Code may be invalid or expired.' });
                }
            } catch (err) {
                await interaction.editReply({ content: `❌ Error: ${err.response?.data?.error || err.message}` });
            }
        }

        if (commandName === 'unlink') {
            await interaction.deferReply({ ephemeral: true });

            try {
                const api = getApiClient();
                await api.post('/api/client/bot/unlink', { discord_id: user.id });

                const embed = new EmbedBuilder()
                    .setTitle('✅ Account Unlinked')
                    .setDescription('Your Discord account has been unlinked from the panel.')
                    .setColor(0xFFA500);
                await interaction.editReply({ embeds: [embed] });
            } catch (err) {
                await interaction.editReply({ content: `❌ Error: ${err.response?.data?.error || err.message}` });
            }
        }

        if (commandName === '2fa') {
            const action = options.getString('action');
            await interaction.deferReply({ ephemeral: true });

            try {
                const api = getApiClient();
                const res = await api.post('/api/client/bot/2fa/toggle', {
                    discord_id: user.id,
                    enabled: action === 'enable',
                });

                if (res.data.success) {
                    const embed = new EmbedBuilder()
                        .setTitle(action === 'enable' ? '🔐 Discord 2FA Enabled' : '🔓 Discord 2FA Disabled')
                        .setDescription(
                            action === 'enable'
                                ? 'You can now use Discord DM codes as 2FA during login!'
                                : 'Discord 2FA has been disabled for your account.'
                        )
                        .setColor(action === 'enable' ? 0x00FF00 : 0xFFA500);
                    await interaction.editReply({ embeds: [embed] });
                }
            } catch (err) {
                await interaction.editReply({ content: `❌ Error: ${err.response?.data?.error || err.message}` });
            }
        }
    });

    try {
        await client.login(discordBotToken);
    } catch (err) {
        console.error('Failed to login to Discord:', err.message);
        console.log('Will retry in 30 seconds...');
        setTimeout(startBot, 30000);
    }
}

startBot();
