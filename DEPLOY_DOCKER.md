# Deployment Guide for EPOS SAZA

## Prerequisites
- Docker & Docker Compose installed
- Network `saza-network` exists
- Environment file `.env` configured

## Setup Steps

1.  **Clone Repository**
    ```bash
    git clone <repository_url>
    cd EPOS_SAZA
    ```

2.  **Environment Configuration**
    Copy the docker environment template and configure secrets:
    ```bash
    cp .env.docker.example .env
    # Edit .env with your production secrets
    ```

3.  **Build and Start**
    ```bash
    docker compose up -d --build
    ```

4.  **Verify Deployment**
    - Check logs: `docker compose logs -f`
    - Check status: `docker compose ps`
    - Visit: https://epos.saza.sch.id

## Maintenance

- **Update Application**:
    ```bash
    git pull
    docker compose build --no-cache epos-app
    docker compose up -d
    ```

- **Run Migrations Manually**:
    ```bash
    docker compose exec epos-app php artisan migrate
    ```

- **Clear Cache**:
    ```bash
    docker compose exec epos-app php artisan optimize:clear
    ```

## Integration with SIMPELS
Use the following hostnames to access SIMPELS services from EPOS container:
- API: `http://simpelssaza-simpelsapi-2ebzdr:8000`
- Frontend: `http://simpelssaza-simpelsfrontend-0bhef9:3000`
- App: `http://simpelssaza-appsimpels-xzwnps:5000`
- Database: `http://simpelssaza-simpelsdb-pq0eoe:3306`
