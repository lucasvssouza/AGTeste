# README — Sistema de Pedidos (Laravel + Blade + API REST)

Aplicação de exemplo para **gestão de pedidos** com:
- **Laravel** (12.x)
- **Blade** (UI com Bootstrap 5, DataTables e SweetAlert2)
- **API REST** (CRUD em `/orders`)
- **Vite** para assets (JS/CSS)

## Requisitos
- **PHP** ≥ 8.2  
- **Composer** ≥ 2.6  
- **MySQL** 
- **Node.js** ≥ **20.19.0** (ou ≥ 22.12.0)  

## Instalação
```bash
git clone <seu-repo>
cd <seu-repo>
composer install
cp .env.example .env
php artisan key:generate
```

Edite `.env` e configure o banco.

## Banco de Dados
```bash
php artisan migrate
php artisan db:seed
```

## Front-end (Vite)
```bash
npm install
npm run dev
```

## Servidor
```bash
php artisan serve
```

## Estrutura
```
app/
    Http/Controllers/
        OrderController.php
     Models/
        Order.php
resources/
  views/
        app.blade.php
        orders/
            index.blade.php
            _form.blade.php
  js/orders/
        orders.js
routes/
     web.php
```
