# b3AisClient

A lightweight PHP client that receives raw **AIS** (Automatic Identification System) data from TCP sources, decodes the NMEA `!AIVDM`/`!AIVDO` sentences, merges the information per vessel and stores the result in **Redis**. The stored data can be inspected in the CLI/browser or displayed on an interactive map.

The project was originally built to monitor vessel traffic at the locks of the Kiel Canal (Brunsbüttel, Kiel and Gieselau), but it works with any AIS feed that is delivered as NMEA sentences over TCP.

## Features

- Connects to one or more AIS sources via TCP socket (`fsockopen`)
- Collects data for a configurable period of time (`request_duration_seconds`)
- Handles messages split across buffer boundaries and multi-part (multi-sentence) AIS messages
- Validates the NMEA checksum of each sentence
- Decodes the following AIS message types:

  | Type    | Description                                  | Extracted data            |
  |---------|----------------------------------------------|---------------------------|
  | 1, 2, 3 | Position report (Class A)                    | MMSI, longitude, latitude |
  | 5       | Static and voyage related data (Class A)     | MMSI, ship name           |
  | 18      | Standard position report (Class B)           | MMSI, longitude, latitude |
  | 19      | Extended position report (Class B)           | MMSI, longitude, latitude, ship name |
  | 24      | Static data report (Class B)                 | MMSI, ship name           |

- Combines name-only and position-only messages into one record per vessel (keyed by MMSI)
- Writes the vessel records as JSON into a Redis list per source
- Logging via [Monolog](https://github.com/Seldaek/monolog) with configurable log level
- Viewer script (CLI and web) and an [OpenLayers](https://openlayers.org/) based map view

## How it works

```
 AIS source (TCP)          processor.php                         Redis
 ────────────────    ┌──────────────────────────┐    ┌───────────────────────────┐
 !AIVDM,1,1,...  ──▶ │ DataFetcher              │    │ ais_data_<port>  (list)   │
 !AIVDM,2,1,...      │  ├─ read socket buffer   │    │  {"name": "...",          │
 !AIVDM,2,2,...      │  ├─ Helper::decode...    │──▶ │   "longitude": 9.14,      │
                     │  │   checksum, payload,  │    │   "latitude": 53.89,      │
                     │  │   6-bit decoding      │    │   "receivedTimestamp": …, │
                     │  └─ merge per MMSI       │    │   "mmsi": 211000000}      │
                     └──────────────────────────┘    └─────────────┬─────────────┘
                                                                   │
                                           viewer.php / Ais/Map/index.php
```

1. `processor.php` opens a TCP connection to the given IP and port.
2. For `request_duration_seconds` the incoming data is read, split into lines and decoded.
3. Messages are merged per MMSI so each vessel has a name and its latest position.
4. When the time is up, the Redis list `ais_data_<port>` is cleared and filled with the new vessel records.

Running the processor periodically (e.g. via cron) keeps the data in Redis up to date.

## Requirements

- PHP 8.1 or newer (tested with 8.1, 8.2, 8.3 and 8.5)
- PHP extensions: `redis` (phpredis), `json`
- A running Redis server
- [Composer](https://getcomposer.org/)

## Installation

```bash
git clone https://github.com/<your-account>/b3AisClient.git
cd b3AisClient
composer install
```

Create the configuration file from the sample:

```bash
cp src/config/config-sample.json src/config/config.json
```

Make sure the log directory is writable (it is created relative to `src/`):

```bash
mkdir -p src/logs
```

## Configuration

`src/config/config.json`:

```json
{
  "logFile": "logs/log.txt",
  "redis_ip": "127.0.0.1",
  "redis_port": "6379",
  "request_duration_seconds": 30,
  "sources": [
    { "port": 31935, "name": "Brunsbüttel" },
    { "port": 31936, "name": "Kiel" },
    { "port": 31937, "name": "Gieselau" }
  ],
  "logLevel": 100
}
```

| Key                        | Description                                                        | Default         |
|----------------------------|--------------------------------------------------------------------|-----------------|
| `logFile`                  | Log file path, relative to `src/`                                  | `logs/ais.log`  |
| `redis_ip`                 | Redis host, or the path of a Unix socket (see below)               | `127.0.0.1`     |
| `redis_port`               | Redis port, ignored for a Unix socket                              | `6379`          |
| `request_duration_seconds` | How long (in seconds) data is collected from the socket per run    | –               |
| `sources`                  | AIS source ports and their display names (see below)               | –               |
| `logLevel`                 | Monolog log level (see table below)                                | `200` (INFO)    |

To connect to Redis via a Unix socket, set `redis_ip` to the absolute path of the socket, e.g. `"redis_ip": "/var/run/redis/redis.sock"`. Any value starting with `/` is treated as a socket path. The processor and the web server user (for `viewer.php` and the map) need read and write access to the socket file; in `redis.conf` this is controlled by `unixsocket` and `unixsocketperm`. Run `php src/RedisTest.php` to check the connection with the current configuration.

`sources` is the central list of AIS source ports. `start_processor.sh` starts one processor per port, `viewer.php` only accepts these ports, and the map shows one section per source with its `name` as heading. The port is also used in the Redis key `ais_data_<port>`.

Log levels: `100` DEBUG, `200` INFO, `250` NOTICE, `300` WARNING, `400` ERROR, `500` CRITICAL, `550` ALERT, `600` EMERGENCY.
Errors are additionally written to `stderr`.

## Usage

### Collect data

Run the processor for a single AIS source:

```bash
php src/processor.php --ip <source-ip> --port <source-port>
```

The processor can only be run from the command line. Called through a web server, it responds with HTTP 403, as it would otherwise allow visitors to open connections to arbitrary addresses.

To query all configured ports of a source in parallel, use `src/start_processor.sh` and pass the source IP as an argument. The ports are read from `sources` in `config.json`:

```bash
./src/start_processor.sh <source-ip>
```

Example cron job that refreshes the data every minute:

```cron
* * * * * /path/to/b3AisClient/src/start_processor.sh <source-ip> >/dev/null 2>&1
```

### View stored data

Print the stored vessel data for one source:

```bash
php src/viewer.php --port 31935
```

Without `--port`, all configured sources are printed. In the browser use `viewer.php?port=31935`. Only ports listed in `sources` are accepted.

### Map view

`src/Ais/Map/index.php` renders the stored vessels of all configured sources on an OpenLayers map (pre-built bundle in `src/Ais/Map/assets/`). Serve the `src/` directory with a PHP-capable web server and open `Ais/Map/index.php`.

## Redis data format

For each source port a Redis list named `ais_data_<port>` is created. Every entry is a JSON object:

```json
{
  "name": "EXAMPLE VESSEL",
  "longitude": 9.1455916666667,
  "latitude": 53.89135,
  "receivedTimestamp": 1700000000,
  "mmsi": 211000000
}
```

`receivedTimestamp` is a Unix timestamp of the last received message for this vessel.

## Project structure

```
src/
├── processor.php            # Entry point: fetch, decode and store AIS data
├── start_processor.sh       # Starts processors for several sources in parallel
├── viewer.php               # Prints stored data (CLI / web)
├── RedisTest.php            # Simple Redis connectivity check
├── config/
│   └── config-sample.json   # Sample configuration
└── Ais/
    ├── Map/                 # OpenLayers map view
    ├── Message/
    │   ├── Helper.php       # NMEA/AIS decoding helpers (checksum, payload, 6-bit, coordinates)
    │   ├── Message.php      # Base message class
    │   └── Message*.php     # Decoders for message types 1/2/3, 5, 18, 19, 24
    └── Request/
        ├── Config.php       # JSON configuration reader
        ├── DataFetcher.php  # Socket reading, decoding, merging and storing
        ├── RedisData.php    # Redis access (connect, clear, write, read)
        └── requestHandler.php # CLI / GET argument parsing
tests/                       # PHPUnit tests
```

## Tests

```bash
vendor/bin/phpunit tests
```

The tests cover the decoding of AIS sentences (message types 1, 5, 18, 24), multi-part messages, checksum validation and the JSON format written to Redis.

`RedisDataTest` needs a running Redis server and is skipped otherwise. It connects to `127.0.0.1:6379` by default; use `REDIS_HOST` (host name or socket path) and `REDIS_PORT` to change this:

```bash
REDIS_HOST=/var/run/redis/redis.sock vendor/bin/phpunit tests
```

## License

This project is licensed under the [Apache License 2.0](LICENSE).
