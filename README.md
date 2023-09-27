# pubmedapi
get document info from pubmedapi if PMID is known

Asks pubmedid from user,
prints document info 

you may get if you query more than 3 documents per second "{"error":"/lbsm/eutils_lb -> Neg","api-key":"xx.yy.zz.abb","type":"ip",
"status":"ok"}"
to be able to query more than 10 documents per seconds you must get an api which is freely available, please read: https://ncbiinsights.ncbi.nlm.nih.gov/2017/11/02/new-api-keys-for-the-e-utilities/

There are both php and javascript code to fetch from publons api.
it will be wise to use an api-key for php code at line:

    $postText="&api_key=Your-API-KEY";

javascript code works from browser, so api-key maynot be necessary. Also your api key must not be visible on other users web browser

you can try those pmid's, some are challenging

35459526
34890845
34282107
35704743
35676203

35079782
35621247
35607918
35791302
35697858
35695908
35639050
35695134

35666833 // difficult for finding real doi

35652580

35599042 // difficult for finding real doi

34815173
35277345
35652880
35653776
35642295
35636973
35604621

555757 // for testing publication year, difficult for javascipt routine

35653776 // for testing abstracts including bold tags, difficult for javascript routine: <b> some text </b>

35666833

32120362 , 24251359 // difficult for authorlist-for javascipt

31258358 // difficult for substring tag <sub> </sub> for javascript
25735358  // difficult, because doi is in ArticleId instead of ELocationID

to-do: Some of the abstracts may be incomplete. It seems not so important.
