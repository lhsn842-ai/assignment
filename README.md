# Laravel Exchange Rate App

A simple Laravel 12 application with PHP 8.2 that supports GraphQL mutations, asynchronous jobs, and real-time updates through WebSockets.

---

## Stack
- **Backend:** Laravel 12, PHP 8.2
- **Database:** MongoDB
- **Real-time:** Soketi / Pusher
- **GraphQL:** Lighthouse

---

## Running Locally

### 1. Start Docker Containers

```bash
docker compose up -d
```
### 2. Enter the App Container
```bash
docker compose exec -it app bash
```
### 3. Run Migrations & Seed Database
```bash
php artisan migrate
php artisan db:seed
```
### 4. Open GraphQL Playground
http://localhost:8000/graphql-playground

### Sample Mutation
Use the following mutation to create a new exchange rate:
```graphql
mutation {
  exchange(input: {
    amount: 100,
    fromCurrency: "EUR",
    toCurrency: "SEK"
  }) {
    statusCode
    message
    data {
      id
      amount
      fromCurrency
      toCurrency
      userId
      created_at
    }
  }
}
```
### How It Works
1. When the mutation is run, the request is inserted into the database.

2. An event is dispatched to trigger a background job.

3. The job calls a third-party service (Swop) to fetch the actual exchange rate.

4. Once the rate is fetched, another event is dispatched.

5. The final event publishes the result to a WebSocket channel, allowing clients to update the UI in real-time.
