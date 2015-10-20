# OC Multishare

This is an Owncloud App. It allows you to duplicate an existing share of some file via API, but the new share will have a different token from the existing share. The new share will also have a fixed expiration date, different from the original share, which is specifiable via the API. When the new share has expired, Owncloud will eventually remove it.

The original share can be a share that exists permanently, or it can be a temporary one -- the choice is yours. The only thing that is required for this app to work properly, is that the share exists, and that the user calling the API has access to it. 

Recommended usage is for developers, in those cases when they wish to issue users a unique time-limited link to a file.  

## Install 
cd /var/lib/owncloud 

unzip oc_multishare.zip

Then enable app in your owncloud instance.

## Example
curl -H "Accept: application/json"  -H "Content-Type: application/json" -X POST -d "{\"id\":\"1325\",\"seconds\":\"1000\"}" -u username:password http://server:8080/owncloud/index.php/apps/ocmultishare/duplicate

"id" here is an ID of a share. You can get the ID by making appropriate requests to the OCS Share API (see https://doc.owncloud.org/server/8.0/developer_manual/core/ocs-share-api.html). Seconds is how many seconds in the future the new share token should expire.

This request will result in response something like this:

{"token":"cs8wMAtM48zG3Vv"}


## Owncloud versions

Tested with Owncloud 8.0.8.
