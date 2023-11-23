# API per co2 risparmiata dalle vendite

## **Prerequisiti:**

### - Per l'utilizzo:

* Specifico front-end per la visualizzazione dei dati.
* Software come Postman che permetta di effettuare richieste ad API.

### - Per lo sviluppo:

* Aver installato PHP e MySql ( es. XAMPP )
* installare `composer` come dependecy manager
* creare apposito file `.ENV` per l’accesso al DB

		Immettere i propri dati nel file .ENV
        
		DB_USER=[username]
        DB_PASSWORD=[password]
        DB_HOST=[host]		// es. Localhost -> 127.0.0.1

</br>

## **Linguaggi utilizzati:** 

* PHP, MySql.

## **Scopo:** 

* Fornire API per gestione dei dati nel database.
* Ricevere i dati filtrati.

</br>

# **Organizzazione dei file:** 

## Cartella `./api`

### In questa cartella vi è il cuore dell’app:

* Contiene il file `index.php`, il quale ricava i dati dall’URI e reindirizza verso l’operazione desiderata utilizzando la funzione contenuta in `file-renderer.php` che a sua volta utilizza lo schema in `routes.php`

* Contiene `./product` e `./sales-order` che sono le due cartelle che contengono i file che si occupano di effettuare le azioni.

## La cartella `./config` contiene:

* Il file `./api_config/routes.php` dove ci sono le regole per lo smistamento. 

* Il file `db_config.php` che crea la configurazione per la connessione al DB utilizzando i dati di accesso presenti in `.ENV`

## La cartella `./core` contiene:

* `Message` -> incaricata a visualizzare messaggi
* `ApiFunctions` -> Tutte le funzioni utili per effettuare controlli e determinate azioni atte a processare i dati
* `UriBuilder` -> costruisce un “oggetto” partendo dall’URI
* `Connection` -> Instaura la connessione al DB partendo da una determinata configurazione.

## La cartella `./model` contiene le classi che sono astrazione dei modelli:

#### `Product` e `Sale`  ->  Ognuno con i suoi metodi **CRUD** e funzioni per le query.

</br>

# Utilizzo delle API

## Richieste “GET” lettura dati

### Ricavare tutti i prodotti o tutti gli ordini:

    GET -> {domain}/api/products/all
    GET -> {domain}/api/sales-orders/all

</br>
<img src = "./readme-img/prod_insert.jpg" style = "width:70%" >

### Ricavare un singolo prodotto od ordine:

    GET -> {domain}/api/products/{id}
    GET -> {domain}/api/sales-orders/{id}

 </br>
 
 <img src = "./readme-img/order_read_id.jpg" style = "width:70%" >

### Ricavare dati da un ordine di vendita:

* Ricavare il totale di tutta la co2 risparmiata da tutte le vendite:
    
        GET ->
        {domain}/api/sales-orders/all-co2

* Ricavare il totale della co2 risparmiata in un determinato intervallo di tempo:
    
        GET ->
        {domain}/api/sales-orders/date-interval-co2?start={start}&end={end}

    #### **N.B.** Lasciando vuoti uno dei due campi `{start}` o `{end}` verranno automaticamente rimpiazzati con la data odierna

    </br>

    <img src = "./readme-img/order_query_date.jpg" style = "width:100%" >
    
    </br>

* Ricavare il totale della co2 risparmiata da tutte le vendite verso un certo paese di destinazione:

        GET ->
        {domain}/api/sales-orders/destination-co2?country={country}   

* Ricavare il totale della co2 risparmiata da uno specifico prodotto:
		
        GET ->
        {domain}/api/sales-orders/product-co2?product={id}

</br>

## Richieste “POST” inserimento dati:

### Inserimento di un nuovo prodotto:

		POST -> {domain}/api/products
        
### Corpo della richiesta: 
    {
        "product_code": "4000",
        "name": "meat",
        "saved_kg_co2":"6"
    }

<img src = "./readme-img/prod_insert.jpg" style = "width:70%" >

</br>

### Inserimento di un nuovo ordine:
	
    	POST -> {domain}/api/sales-orders

### Corpo della richiesta: 

    {
        "sales_code": "AA0000",
        "sales_date": "2023-11-13 09:36:20",
        "destination": "Italy",
        "product_id": "5520, 0100"
    }	

</br>

## Richieste “PUT” modifica dei dati:

### Modifica di un prodotto:

		PUT -> {domain}/api/products/product?code={value}

#### esegue l'update del prodotto con il codice = {value}
#### Nel corpo della richiesta possono essere specificati tutti i campi o solo quelli interessati:

### Corpo della richiesta: 

    {
        "product_code": "4141",
        "name": "meat",
        "saved_kg_co2":"7"
    }

</br>

### Modifica di un ordine di vendita:

		PUT -> {domain}/api/sales-orders/order?code={value}

#### esegue l'update dell’ordine con il codice = {value}
#### Nel corpo della richiesta possono essere specificati tutti i campi o solo quelli interessati:

### Corpo della richiesta: 

    {
        "sales_code": "BB0000",
        "sales_date": "2023-11-14",
        "destination": "USA",
        "product_id": "5520, 0100, 0234"
    }

#### O semplicemente modificare solo il paese di destinazione

    {
        "destination": "USA",
    }

<img src = "./readme-img/order_update.jpg" style = "width:70%" >

</br>

## Richieste “DELETE” cancellazione dei dati:

### Cancellazione di un prodotto:

		DELETE -> {domain}/api/products/product?code={value}

#### esegue il delete del prodotto con il codice = {value}

<img src = "./readme-img/prod_delete.jpg" style = "width:70%" >

</br>

### Cancellazione di un ordine di vendita:

		DELETE -> {domain}/api/sales-orders/order?code={value}

#### esegue il delete dell’ordine con il codice = {value}

</br>

## Possibili errori

### Ordine già esistente:

<img src = "./readme-img/order_error_insert.jpg" style = "width:70%" >

</br>

# **Link utili:**

### - [Sito personale (portfolio)](https://giovannipacelli2.github.io/portfolio/)

### - [Breve presentazione](./presentation/Presentazione_API.pdf)