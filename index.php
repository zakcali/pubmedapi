<!DOCTYPE html>
<!-- bu yazılım Dr. Zafer Akçalı tarafından oluşturulmuştur 
programmaed by Zafer Akçalı, MD -->
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Pubmed numarasından makaleyi bul</title>
</head>

<body>
<a href="PMID nerede.png" target="_blank"> PMID nereden bakılır? </a>
<form method="post" action="">
pubmed id (PMID)  numarasını giriniz<br/>
<input type="text" name="pmid" id="pmid" ">
<input type="submit">
</form>
<button id="pubmedGoster" onclick="pubmedGoster()">Pubmed yayınını göster</button>
<br/>
<?php
// pubmedapi V1.0
// By Zafer Akçalı, MD
// Zafer Akçalı tarafından programlanmıştır

if (isset($_POST['pmid'])) {
$girilenveri=trim($_POST["pmid"]);

if($girilenveri!=""){
	
$preText="https://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?db=pubmed&id=";
$pmid=$girilenveri;
$url=$preText.$pmid;
// https://ncbiinsights.ncbi.nlm.nih.gov/2017/11/02/new-api-keys-for-the-e-utilities/
// saniyede 10'dan fazla sorgu için, api-key alarak aşağıdaki iki satırı açmalısınız: 
// $postText="&api_key=ABCD1234";
// $url = $url.$postText;
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_URL, $url);
$data=curl_exec($ch);
curl_close($ch);
echo ('</>');
$xml_object = simplexml_load_string($data);
$xml_array=json_decode(json_encode($xml_object),1);
//echo($data);
//print_r ($xml_array);
echo ('PMID: ');
echo ($xml_array['PubmedArticle']['MedlineCitation']['PMID']);
echo ('<br/>');

echo ('Makalenin başlığı: ');
echo ($xml_array['PubmedArticle']['MedlineCitation']['Article']['ArticleTitle']);
echo ('<br/>');

echo ('doi: ');
if (!is_array ($xml_array['PubmedArticle']['MedlineCitation']['Article']['ELocationID']))
echo ($xml_array['PubmedArticle']['MedlineCitation']['Article']['ELocationID']);
	else echo ($xml_array['PubmedArticle']['MedlineCitation']['Article']['ELocationID'][0]);
echo ('<br/>');

echo ('Dergi ismi: ');
// : kullanılmışsa gersini kaldır at
$dergi = $xml_array['PubmedArticle']['MedlineCitation']['Article']['Journal']['Title'];
if (strpos($dergi, " :") !== false)
	$dergi = substr($dergi, 0, strpos($dergi, " :"));
echo ($dergi);
echo ('<br/>');

echo ('Derginin kısa ismi: ');
echo ($xml_array['PubmedArticle']['MedlineCitation']['Article']['Journal']['ISOAbbreviation']);
echo ('<br/>');

echo ('ISSN: ');
if (isset ($xml_array['PubmedArticle']['MedlineCitation']['MedlineJournalInfo']['ISSNLinking']))
	echo ($xml_array['PubmedArticle']['MedlineCitation']['MedlineJournalInfo']['ISSNLinking']);
echo ('<br/>');

echo ('eISSN: ');
if (isset ($xml_array['PubmedArticle']['MedlineCitation']['Article']['Journal']['ISSN']))
	print_r ($xml_array['PubmedArticle']['MedlineCitation']['Article']['Journal']['ISSN']);
echo ('<br/>');

echo ('Yıl: ');
echo ($xml_array['PubmedArticle']['MedlineCitation']['Article']['Journal']['JournalIssue']['PubDate']['Year']);
echo ('<br/>');

echo ('Cilt: ');
if (isset ($xml_array['PubmedArticle']['MedlineCitation']['Article']['Journal']['JournalIssue']['Volume']) )
	echo ($xml_array['PubmedArticle']['MedlineCitation']['Article']['Journal']['JournalIssue']['Volume']);
echo ('<br/>');

echo ('Sayı: ');
if (isset ($xml_array['PubmedArticle']['MedlineCitation']['Article']['Journal']['JournalIssue']['Issue']))
	echo ($xml_array['PubmedArticle']['MedlineCitation']['Article']['Journal']['JournalIssue']['Issue']);
echo ('<br/>');

echo ('Başlangıç sayfası: ');
if (isset ($xml_array['PubmedArticle']['MedlineCitation']['Article']['Pagination']['StartPage']))
	echo ($xml_array['PubmedArticle']['MedlineCitation']['Article']['Pagination']['StartPage']);
echo ('<br/>');

echo ('Bitiş sayfası: ');
if (isset($xml_array['PubmedArticle']['MedlineCitation']['Article']['Pagination']['EndPage']))
	echo ($xml_array['PubmedArticle']['MedlineCitation']['Article']['Pagination']['EndPage']);
echo ('<br/>');

if (isset ( $xml_array['PubmedArticle']['MedlineCitation']['Article']['AuthorList']['Author']['ForeName']))
// tek yazar var
{
	echo ('Yazar: ');
	echo ($xml_array['PubmedArticle']['MedlineCitation']['Article']['AuthorList']['Author']['ForeName']).' ';
	echo ($xml_array['PubmedArticle']['MedlineCitation']['Article']['AuthorList']['Author']['LastName']);
echo ('<br/>');	
echo ('Yazar sayısı: 1');
echo ('<br/>');	
}
else {  // birden fazla yazar var
$yazarlar = "";
$n=0;
$count = count ($xml_array['PubmedArticle']['MedlineCitation']['Article']['AuthorList']['Author']);
//echo ($count);
for  ($i=0; $i<$count; $i++) {
	if (isset ($xml_array['PubmedArticle']['MedlineCitation']['Article']['AuthorList']['Author'][$i]['ForeName'] )) {
		// CollectiveName - Grup İsmi yok
		$author = $xml_array['PubmedArticle']['MedlineCitation']['Article']['AuthorList']['Author'][$i]; 
		$yazarlar = $yazarlar.$author['ForeName'].' '.$author['LastName'].', ';
		$n=$n+1;
		
		}
}
echo ('Yazar sayısı: '.$n.'<br/>');
echo ('Yazarlar: ');
echo substr ($yazarlar,0,-2);
}
echo ('<br/>');

echo ('Yayın türü: ');
if (isset ($xml_array['PubmedArticle']['MedlineCitation']['Article']['PublicationTypeList']['PublicationType'][0])) {
		if (strlen($xml_array['PubmedArticle']['MedlineCitation']['Article']['PublicationTypeList']['PublicationType'][0])>1 )
			print_r ($xml_array['PubmedArticle']['MedlineCitation']['Article']['PublicationTypeList']['PublicationType'][0]);
}
echo ('<br/>');
// print_r ($xml_array['PubmedArticle']['MedlineCitation']);

}
}

?>
<script>
function pubmedGoster() {
var	w=document.getElementById('pmid').value.replace(" ","");
	urlText = "https://pubmed.ncbi.nlm.nih.gov/"+w;
	window.open(urlText,"_blank");
}
</script>
</body>
</html>
