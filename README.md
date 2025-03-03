# VicFlora Images

This application manages image metadata from the Canto Integration API in
VicFlora.

The following command reloads all records from the `/images` endpoint of the
Canto Integration API. We run that every night.

```bash
php vicflora-images app:update-images-table --all
```
The following command only gets the records that have been updated since the
most recently updated record in the database was updated. This command we run
every couple of minutes.

```bash
php vicflora-images app:update-images-table
```
