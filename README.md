# SmartCellar

# Welcome to My Event Management App

This project was created for the Turing College specialization sprint in PHP. It is the fourth project in the series.

## Part 0 - Setup

To run this project, you will need to:

1. Download Docker on your machine.
2. Set up a database and connect it through the environment file values:
    - `DB_HOST`: Your database host.
    - `DB_NAME`: The name of your database.
    - `DB_USER`: The user for your database.
    - `DB_PASS`: The password for your database user.
    - `DATABASE_URL`: To set up your Doctrine connection.
    - `REDIS_URL`: Your Redis Config path.
    - `MESSENGER_TRANSPORT_DSN`: The setup for your Messenger DSN.
    - `API_KEY`: Your API key from Cloudmersive Barcode API. https://cloudmersive.com/barcode-api
    - `API_KEY_LOOKUP`:Your API key from Barcode Lookup. https://www.barcodelookup.com/

## Part 1 - Initializing and Migrating

Run `docker-compose up` and navigate to your chosen localhost port.
.
Enter the Docker container running the project and execute the following command to create the database tables:

```
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

Then execute the following command to install all the project dependencies:

```
composer install
```

## Part 2 - Sign Up/Login

To create an account: The app should automatically send you to the login page if you are not logged in. From there,
click the “Create an account” link at the bottom of the page, create an account, and log in.

## Part 3 - Create a Storage

After logging in with an account, you will be redirected to the ‘Storages’ page. There you can select to create a new
storage and you’ll be redirected to the create storage page. Here you’ll only see one field:

- `name`: You can give it any name you want at this point, as there are no limitations for this right now.

Once you have chosen the name, you can press the submit button.

## Part 4 - Add Your Products

Now that you have a storage, you can add products into that storage using two methods:

- `add barcode`: You can manually insert the barcode.
- `add photo`: You can take a photo of the barcode.

In both methods, you press send and it will send the information to the appropriate messenger bus. From there, it will
retrieve the
barcode and if it exists in the database already, you’ll have every field filled. If it doesn’t but is registered in the
second API
(Barcode Lookup), you’ll also retrieve the title/name and category of the product. However, it will request you to fill
those spaces
before you save into the repository. If you pass the same barcode multiple times, it will increase the quantity to be
stored, so for
every product you want to add, you need to input the barcode.

This will store your currently inserted items into the cache. From here, once you press the temporary button of ‘Let’s
finish this’,
it will save your products and quantity. If your products are not registered in the database yet, it will ask you to
fill in the title
and category names.

## Part 5 - Extract Your Products

You can extract/reduce the number of products into that storage using two methods:

- `extract barcode`: You can manually insert the barcode.
- `extract photo`: You can take a photo of the barcode.

In both methods, you press extract and it will send the information to the appropriate messenger bus.
From there, it will retrieve the barcode and if it exists in your storage, you’ll reduce by one the quantity inside that
storage.

### Part 6 - The Current State of Affairs

This is my capstone project for the PHP specialization module. My idea was to create a simple-to-use inventory system
that
anyone could use at home, but that could also handle multiple data for companies. At this point, this is an MVP
(Minimum Viable Product) that handles user login and registration, as well as product and storage registration and
security
(it’s not possible for a user without authorization to access a storage).

It is also ready for the case of two people inserting data into the same storage at the same time (that’s why it first
keeps the
new products inside the cache). However, it’s not possible to have two people with the same storage at the moment.

In the future, I’d like this to also create a list so that the user can manage what products were inserted and extracted
on
which dates. I also intend for the user to be able to set a minimum quantity so that if the stock gets below that point,
it will
alert the user and add an item to a shopping list so the user can automatically see what’s missing in their fridge.

This took me a long time because of the APIs. It took me 2 days to understand that the reason it was not working was
because the photo API was terrible, and as such, a necessity arose to be able to manually insert the barcodes. Also, the
Barcode
Lookup API for testing purposes gives you something like 10 requests, which I wasted in the first tests, so I had to add
the
possibility of keeping everything inside our own database instead of just using that API.

Overall, this was a very satisfying project to work on. It’s not finished yet, but I intend to improve it in the next
few weeks so it can be at the point where I want to use it at home.

Enjoy the app!

Best Regards,
