## Synopsis

Fury Bulletin Board System (fbbs).

## Installation

Get sqlite3 command line:

sudo apt-get sqlite3

Create user database in sqlite:

touch fbbs-user.db; chmod a+wr fbbs-user.db

Create users table in fbbs.db in sqlite3:

sqitee3 fbbs-user.db
> 
CREATE TABLE users(id INTEGER PRIMARY KEY ASC, username TEXT UNIQUE NOT NULL, password TEXT NOT NULL, timestamp INTEGER NOT NULL);
CREATE INDEX username_idx ON users(username);

## Usage



