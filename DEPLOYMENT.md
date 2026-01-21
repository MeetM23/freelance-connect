# Deployment Guide

This guide outlines the steps to deploy the Freelance Connect application using a split architecture:
- **Backend**: PHP on Render (Native PHP support)
- **Frontend**: React on Vercel (Best-in-class frontend hosting)

## Part 1: Backend Deployment (Render)

Render provides excellent native support for PHP applications.

1.  **Push Code to GitHub**: Ensure your project is pushed to a GitHub repository.
2.  **Create a New Web Service**:
    *   Log in to [Render](https://render.com).
    *   Click "New" -> "Web Service".
    *   Connect your GitHub repository.
3.  **Configure Service**:
    *   **Name**: `freelance-connect-api` (or similar)
    *   **Runtime**: `PHP`
    *   **Build Command**: `true` (We don't need a build step for raw PHP, or `composer install` if you add dependencies later)
    *   **Start Command**: `php -S 0.0.0.0:10000` (This uses the built-in PHP server for simplicity, or configure Apache/Nginx if preferred)
4.  **Environment Variables**:
    Add the following environment variables in the Render dashboard:
    *   `DB_HOST`: Your database host
    *   `DB_USER`: Your database user
    *   `DB_PASS`: Your database password
    *   `DB_NAME`: Your database name
    *   `FRONTEND_URL`: The URL of your Vercel frontend (e.g., `https://freelance-connect-frontend.vercel.app`) - *You can update this after deploying the frontend.*

## Part 2: Frontend Deployment (Vercel)

1.  **Prepare Frontend**: Ensure your React frontend is in a separate directory or repository (recommended).
2.  **Deploy to Vercel**:
    *   Log in to Vercel.
    *   Import your frontend repository.
    *   **Environment Variables**:
        *   `REACT_APP_API_URL`: The URL of your deployed Render backend (e.g., `https://freelance-connect-api.onrender.com`)

## Part 3: Connecting Them

1.  **Update Backend CORS**: Once your frontend is deployed, go back to Render and update the `FRONTEND_URL` environment variable with your actual Vercel URL.
2.  **Update Frontend API URL**: Ensure your frontend makes requests to the `REACT_APP_API_URL`.

## Database

For the database, you can use a managed MySQL service like:
*   **PlanetScale** (Serverless MySQL)
*   **Aiven** (Free tier available)
*   **Render** (Managed PostgreSQL, but you'd need to switch from MySQL to Postgres or use a Docker container for MySQL)
*   **Clever Cloud** (Free MySQL tier)

## Troubleshooting

*   **CORS Errors**: Double-check that `FRONTEND_URL` in Render matches your Vercel URL exactly (no trailing slashes).
*   **Database Connection**: Verify your `DB_HOST` and credentials. Ensure your database allows external connections if it's hosted separately.
