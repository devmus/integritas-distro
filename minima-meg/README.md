# Added comments:

First: Had to run mysql action:update manually in node terminal
Second: Had to run mysqlcoins action:update manually in node terminal

Can / should this be automated?

# meganode

MySQL backed Minima node with MEG as a multi-container docker image

Public MDS is also enabled by default and can be accessed in the usual way at https://127.0.0.1:9003/public/

### Installation

You must have docker installed.

Clone this repo

```
git clone https://github.com/minima-global/meganode.git
```

There are certain values in the **docker-compose.yml** file you may want to edit

- The MDS password for Minima
- The admin password for MEG
- The Minima / MEG / MySQL ports and if they should be exposed
- The initial peers to connect to can be set up later or directly from the params passed to Minima

You can of course change _any_ parameters you want - just add / remove them as environment variables

Once you have set those up run

```
docker-compose up -d
```

And thats it.. you will now have a MegaMMR Minima node that is backed by a MySQL database

MEG will be running - and all the plumbing is set up auto-magically so that MEG and Minima can talk to each other

Startup takes about ~30 seconds.. before you can login to MDS or use MEG - as they both wait for mysql and each other to start

The Minima data folder is mapped to the local **data** folder

A daily backup of your MegaMMR and MySQL database is put in **backups**

### MegaMMR

If you wish to have ALL the coin proofs ( you run a public wallet or are an exchange ) it is very fast and easy to import the coin data.

Copy a megammr file into the data folder ( find one on [spartacusrex.com](https://spartacusrex.com) )

Open Terminal in MDS and run :

```
megammr action:import file:minima_megammr_date.mmr
```

You will now have a complete Minima node with all coin proofs.

### Historical Data

This setup will record all data / TxPoW from the moment you start it

If you wish to load ALL old TxBlock data aswell - you will need to :

- Copy a ..raw.dat backup into the data folder ( find one on [spartacusrex.com](https://spartacusrex.com) )
- Open Terminal on MDS
- run

```
mysql action:reset file:minima_archive_date.raw.dat
```

This will do a mysql rawimport and then a resync in one go..

This process will take a long time.. hours.. you can see the how far by looking either at the Minima logs in docker or turn logs on in Terminal.

You will now have a complete system with ALL data.
