# Stock Manager API

Sistema backend per la gestione e il monitoraggio di ordini generici di uno o più prodotti

## 🌟 Panoramica

Il **Stock Manager API** è un sistema backend sviluppato in **PHP Laravel** che gestisce ordini. Il sistema è progettato per supportare un frontend che
implementa funzionalità di visualizzazione, ricerca avanzata e gestione degli ordini.

### Tecnologie Utilizzate

- **Framework**: Laravel 12 (PHP 8.4)
- **Database**: MySQL 8.0
- **Containerizzazione**: Docker
- **Architettura**: Clean Architecture usando Eloquent al posto del Repository pattern
- **Testing**: PHPUnit con Feature Tests
- **API Documentation**: OpenAPI/Swagger
- **Caching**: Database-based caching per performance ottimizzate

## 🏗️ Architettura

Il progetto segue i principi della **Clean Architecture** con una chiara separazione delle responsabilità:

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/          # API Controllers
│   │   └── Requests/            # Request Validation
│   ├── UseCases/                # Business Logic Layer
│   ├── Services/                # Domain Services
│   ├── Repositories/            # Data Access Layer
│   ├── Gateways/               # External Services Interface
│   ├── Models/                 # Eloquent Models
│   ├── Dtos/                   # Data Transfer Objects
│   ├── Enums/                  # Application Enumerations
│   └── UnitOfWork/             # Transaction Management
├── database/
│   ├── migrations/             # Database Schema
│   ├── seeders/               # Data Seeders
│   └── factories/             # Model Factories
└── tests/
    ├── Feature/               # Integration Tests
```

### Porte Utilizzate

- **81**: Applicazione Laravel
- **3307**: Database MySQL (per evitare conflitti con installazioni locali)

## 🚀 Installazione e Setup

### 1. Clone del Repository

### 2. Avvio con Docker Compose

```
docker compose up --build -d
```

### 3. Setup del Database

Già gestito dall'entrypoint del docker-compose

### 4. Verifica dell'Installazione

```
http://localhost:81/api/orders?page=1&rowsPerPage=5
``` 

### 5. Verifica Database

Per vedere il database usato, collegarsi in locale alla porta **3307** con un client sql all'istanza mysql.

Usare le credenziali user: **stockAdmin** e password: **passwordAdmin**

### 6. Test Suite

Per lanciare la suite di test lanciare il comando

```bash
   composer run test
``` 

### 7. Variabili d'Ambiente

Il progetto utilizza le seguenti configurazioni principali (gestite automaticamente via Docker):

```env
APP_NAME=StockManager
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:80

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=stock
DB_USERNAME=stockAdmin
DB_PASSWORD=passwordAdmin

CACHE_STORE=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database
```

## 📚 API Documentation

### Endpoint Principali

#### Gestione Ordini

- `GET /api/orders` - Lista ordini con filtri e paginazione
- `GET /api/orders/{id}` - Dettaglio ordine specifico
- `POST /api/orders` - Creazione nuovo ordine
- `PUT /api/orders/{id}` - Aggiornamento ordine esistente
- `DELETE /api/orders/{id}` - Cancellazione ordine (soft delete)

### Documentazione Swagger

Una volta avviato il progetto, accedere alla documentazione interattiva:

- **URL**: `http://localhost:81/api/documentation`

# Considerazioni Tecniche

## Architettura e Design Pattern

**Gestione delle rotte prodotti**: Le rotte per la gestione dei prodotti non sono state esposte, assumendo che non rientrassero nello scope del dominio del progetto. Tuttavia, grazie all'architettura implementata, l'aggiunta di queste rotte risulta di semplice implementazione. Per la fase di test, è possibile accedere direttamente al database per recuperare gli ID dei prodotti necessari.

**Pattern di persistenza**: È stato preferito l'utilizzo di Eloquent rispetto al repository pattern per sfruttare le comodità offerte dall'ORM. Questa scelta, pur vincolando la logica di dominio a quella di persistenza, si giustifica considerando le dimensioni del progetto e i vantaggi forniti dall'ecosistema Laravel. Per progetti di maggiore complessità, è opportuno valutare l'adozione del repository pattern.

## Gestione della Concorrenza

È stata implementata la logica **Unit of Work** negli use case per garantire l'integrità del database e gestire la concorrenza tra richieste multiple:

```php
// Prevenzione di possibili deadlock con richieste concorrenti
// sugli stessi prodotti - scenario limite ma importante da gestire
$products = Product::query()
    ->whereIn('id', $productIds)
    ->lockForUpdate()
    ->get()
    ->keyBy('id');
```

Il lock viene acquisito all'inizio della transazione poiché l'unlock avviene automaticamente al commit o rollback. Questo approccio previene potenziali deadlock tra richieste concorrenti sulle stesse risorse. Sebbene possa introdurre una latenza maggiore, garantisce la consistenza dei dati.

## Semplificazioni e Assunzioni

**Status degli ordini**: È stato assunto che lo status dell'ordine è sempre "delivered", semplificando la logica di business. In futuro sarà possibile implementare flussi diversi basati sui vari stati dell'ordine.

**Controllo attivazione prodotti**: È stato aggiunto un attributo `isActive` per permettere l'attivazione/disattivazione runtime della disponibilità all'acquisto dei prodotti.

**Sistema di autenticazione**: Non è stato implementato un sistema di autenticazione per semplificare lo sviluppo. In produzione sarà necessario implementare meccanismi di autenticazione appropriati (OAuth, Bearer Token, etc.).

## Performance e Ottimizzazioni

**Caching**: È stata implementata una strategia di cache per migliorare l'efficienza del sistema. Per performance superiori, si raccomanda l'implementazione di strumenti come ElasticSearch, che offrirebbe anche ricerche più robuste e tolleranti agli errori di digitazione. Inoltre, è possibile aggiungere al docker-compose il servizio Redis per migliorare le performance delle operazioni di cache, dato che attualmente utilizza il driver database. Il codice è stato strutturato per supportare entrambe le soluzioni senza modifiche.

**Strategia di indicizzazione**: È stata presa la decisione di non indicizzare il campo `description`, poiché l'overhead di un indice su questo tipo di dato supererebbe i benefici reali, specialmente considerando una futura implementazione di ElasticSearch. L'integrazione di quest'ultimo sarebbe facilitata da librerie come Laravel Scout.

## Testing e Deployment

**Strategia di test**: Sono stati implementati esclusivamente feature test che coprono la maggior parte degli edge case identificati.

**Configurazione Docker**: La configurazione Docker simula un deployment di produzione con Nginx, mantenendo tuttavia le variabili d'ambiente in modalità debug per comodità durante la fase di verifica. In produzione sarà necessario un fine-tuning della configurazione in base all'ambiente target e l'integrazione con pipeline CI/CD appropriate.

**Code quality**: È stata implementata una strategia di qualità del codice attraverso due strumenti principali:

- **Laravel Pint**, utilizzato per il code formatting e linting automatico, Il comando è configurato nel composer.json ed è eseguibile tramite composer run lint.
- **PHPStan**, implementato per l'analisi statica del codice con un livello di controllo conservativo, facilmente incrementabile per pipeline CI/CD più rigorose, è eseguibile tramite composer run analyse.
## Il bug più fastidioso riscontrato

È stato necessario modificare l'`entrypoint.sh` per implementare un controllo ciclico della connessione MySQL con intervalli di 3 secondi. Nonostante l'utilizzo di `depends_on` nel servizio MySQL, il container dell'applicazione si avviava prima che il database fosse effettivamente pronto ad accettare connessioni.

Il problema principale era che in caso di timeout di connessione, l'applicativo scriveva nei log con privilegi di root, compromettendo i permessi dei file di log. Questo causava errori nelle successive operazioni di logging, poiché il processo PHP-FPM (che gira come `www-data`) non riusciva più a scrivere nei file precedentemente creati da root.

La soluzione ha richiesto:
1. Implementazione di un wait-loop nell'entrypoint per verificare la disponibilità di MySQL
2. Reset dei permessi dei file di log assegnandoli a `www-data` con i corretti privilegi di lettura/scrittura

Questo garantisce che l'applicazione si avvii solo quando il database è effettivamente disponibile e mantiene la coerenza dei permessi durante tutto il ciclo di vita del container.
