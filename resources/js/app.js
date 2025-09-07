import './bootstrap';
import axios from 'axios';
import {initEcho} from './echo';

function getCookie(name) {
    const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
    return match ? decodeURIComponent(match[2]) : null;
}

const loginForm = document.getElementById('loginForm');
if (loginForm) {
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;

        const query = `
        mutation {
            login(email: "${email}", password: "${password}") {
                status
                message
                token
                user {
                    id
                    name
                    email
                }
            }
        }
    `;

        try {
            await axios.get('/sanctum/csrf-cookie', { withCredentials: true });

            const response = await axios.post('/graphql', { query }, { withCredentials: true });

            const loginData = response.data.data.login;

            if (loginData && loginData.status === 200) {
                localStorage.setItem('token', loginData.token);
                localStorage.setItem('userId', loginData.user.id);
                window.location.href = '/dashboard';
            } else {
                errorMsg.textContent = loginData?.message || 'Invalid credentials';
                errorMsg.classList.remove('hidden');
            }
        } catch (err) {
            console.error(err);
            errorMsg.textContent = 'An error occurred. Try again.';
            errorMsg.classList.remove('hidden');
        }
    });
}

// --- DASHBOARD LOGIC ---
const exchangeForm = document.getElementById('exchangeForm');
if (exchangeForm) {
    const resultDiv = document.getElementById('result');
    const token = localStorage.getItem('token');
    const userId = localStorage.getItem('userId');
    console.log('Dashboard loaded with userId:', userId);

    if (!token || !userId) {
        window.location.href = '/login';
    }

    initEcho().then(() => {
        const channelName = 'user.' + userId;

        const channel = window.Echo.channel(channelName);

        channel.subscribed(() => {
            console.log(`✅ Successfully subscribed to ${channelName}`);
        });

        channel.error((error) => {
            console.error(`Subscription error for ${channelName}:`, error);
        });

        channel.listen('.ExchangeRateResultReadyEvent', (payload) => {
            document.getElementById('resultP').innerHTML = `<p style="color: mediumseagreen">Exchange result: ${payload.result}</p>`;
        });

    }).catch(error => {
        console.error('Failed to initialize Echo:', error);
    });

    exchangeForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const xsrfToken = getCookie('XSRF-TOKEN');

        const amount = parseFloat(document.getElementById('amount').value);
        const fromCurrency = document.getElementById('from_currency').value;
        const toCurrency = document.getElementById('to_currency').value;

        resultDiv.innerHTML = `<p id="resultP" style="color: aquamarine">Waiting for conversion result...</p>`;

        const query = `
        mutation {
            exchange(input: {
                amount: ${amount},
                fromCurrency: "${fromCurrency}",
                toCurrency: "${toCurrency}"
            }) {
                statusCode
                message
                data {
                    id
                    amount
                    fromCurrency
                    toCurrency
                    result
                    userId
                    created_at
                }
            }
        }`;

        try {
            const response = await axios.post(
                '/graphql',
                { query },
                {
                    withCredentials: true,
                    headers: {
                        'X-XSRF-TOKEN': xsrfToken,
                        'Authorization': `Bearer ${token}`
                    }
                }
            );

            const exchangeData = response.data.data.exchange;

            if (exchangeData.statusCode === 201) {
                if (exchangeData.data.result) {
                    document.getElementById('resultP').innerHTML = 'Fetched from cache';
                    resultDiv.innerHTML += `
                    <p class="text-green-600 font-semibold">${exchangeData.message}</p>
                    <p>From: ${exchangeData.data.fromCurrency}</p>
                    <p>To: ${exchangeData.data.toCurrency}</p>
                    <p>Amount: ${exchangeData.data.amount}</p>
                    <p>Result: ${exchangeData.data.result}</p>
                    <p>Created at: ${exchangeData.data.created_at}</p>`;
                } else {
                    resultDiv.innerHTML += `
                    <p class="text-green-600 font-semibold">${exchangeData.message}</p>
                    <p>From: ${exchangeData.data.fromCurrency}</p>
                    <p>To: ${exchangeData.data.toCurrency}</p>
                    <p>Amount: ${exchangeData.data.amount}</p>
                    <p>Created at: ${exchangeData.data.created_at}</p>`;
                }
            } else {
                resultDiv.innerHTML = `<p class="text-red-500">Error: ${exchangeData.message}</p>`;
            }
        } catch (err) {
            console.error(err);
            resultDiv.innerHTML = `<p class="text-red-500">An error occurred. Try again.</p>`;
        }
    });
}
