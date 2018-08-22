# Tickets API

This repository contains the source code for a small Symfony application that allows users to create listings with tickets for sale, verify listings, and buy tickets.

## Setup

Clone the repository, then simply run `php bin/console server:run`.
You can then make API calls to `localhost:8000`.

## Functionality

This application is database-agnostic (no persistence layer), so API calls that rely on existing persisted data may not yield exactly the results you want :).
Regardless, here are the API's 3 endpoints:

- `POST /listings/create` to create a new listing. 
Example request body:
```json
{
	"seller": "alice",
	"barcodes": [
		{
			"type": "xxx",
			"value": "001"
		},
		{
			"type": "xxx",
			"value": "002"
		}
	],
	"price": {
		"amount": 50,
		"currency": "EUR"
	}
}
```


- `POST /listings/{listingId}/verify` to verify a listing.
```json
{
	"verifier": "liza"
}
```


- `POST /tickets/{ticketId}/buy` to buy a ticket.
```json
{
	"buyer": "ana"
}
```
