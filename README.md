# gbg_voting
Voting for Good Beer Guide Pubs

This page allows sufficiently authorised people to vote for their favourite Pub. Authorisation comes from a API provided by CAMRA HQ.

The list of pubs that can be voted for is stored in DB, this list is then processed and displayed on the page. If the user has previously voted for a given pub, the checkbox is pre-ticked allowing them to change their mind. 

For ease of processing, the pubs voted for are encoded into a JSON string and then written to a Database by the gbg_submit.php. A timestamp is also written to the DB along with the membership number of the given user, this is simply updated if the user re-submits. A confirmation email is also sent to the user confirming their votes..

The results page shows a breakdown of the pubs voted for, including:
The total number of votes recieved;
The total number of Pubs voted for.

The results page retrives all votes submitted, decodes the JSON into an array. The pub is then pushed to another array if it hasnt been voted for previously.
