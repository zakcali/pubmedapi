<!DOCTYPE html>
<!-- bu yazılım Dr. Zafer Akçalı tarafından oluşturulmuştur 
programmed by Zafer Akçalı, MD -->
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Pubmed numarasından makaleyi bul</title>
</head>

<body>
<?php
// pubmedapi V2.0
// By Zafer Akçalı, MD
// Zafer Akçalı tarafından programlanmıştır
$PMID=$doi=$ArticleTitle=$dergi=$ISOAbbreviation=$ISSN=$eISSN=$Year=$Volume=$Issue=$StartPage=$EndPage=$yazarlar=$PublicationType=$AbstractText="";
$yazarS=0;
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
$xml_object = simplexml_load_string($data);
$xml_array=json_decode(json_encode($xml_object),1);

$PMID=($xml_array['PubmedArticle']['MedlineCitation']['PMID']);

if (!is_array ($xml_array['PubmedArticle']['MedlineCitation']['Article']['ELocationID']))
	$doi= ($xml_array['PubmedArticle']['MedlineCitation']['Article']['ELocationID']);
else $doi = ($xml_array['PubmedArticle']['MedlineCitation']['Article']['ELocationID'][0]);

$ArticleTitle= $xml_array['PubmedArticle']['MedlineCitation']['Article']['ArticleTitle'];

$dergi = $xml_array['PubmedArticle']['MedlineCitation']['Article']['Journal']['Title'];
if (strpos($dergi, " :") !== false) // : kullanılmışsa gersini kaldır at
	$dergi = substr($dergi, 0, strpos($dergi, " :"));

$ISOAbbreviation= $xml_array['PubmedArticle']['MedlineCitation']['Article']['Journal']['ISOAbbreviation'];

if (isset ($xml_array['PubmedArticle']['MedlineCitation']['MedlineJournalInfo']['ISSNLinking']))
	$ISSN=$xml_array['PubmedArticle']['MedlineCitation']['MedlineJournalInfo']['ISSNLinking'];

if (isset ($xml_array['PubmedArticle']['MedlineCitation']['Article']['Journal']['ISSN']))
	$eISSN=$xml_array['PubmedArticle']['MedlineCitation']['Article']['Journal']['ISSN'];

$Year =$xml_array['PubmedArticle']['MedlineCitation']['Article']['Journal']['JournalIssue']['PubDate']['Year'];

if (isset ($xml_array['PubmedArticle']['MedlineCitation']['Article']['Journal']['JournalIssue']['Volume']) )
	$Volume=$xml_array['PubmedArticle']['MedlineCitation']['Article']['Journal']['JournalIssue']['Volume'];

if (isset ($xml_array['PubmedArticle']['MedlineCitation']['Article']['Journal']['JournalIssue']['Issue']))
	$Issue= $xml_array['PubmedArticle']['MedlineCitation']['Article']['Journal']['JournalIssue']['Issue'];

if (isset ($xml_array['PubmedArticle']['MedlineCitation']['Article']['Pagination']['StartPage']))
	$StartPage= $xml_array['PubmedArticle']['MedlineCitation']['Article']['Pagination']['StartPage'];

if (isset($xml_array['PubmedArticle']['MedlineCitation']['Article']['Pagination']['EndPage']))
	$EndPage= $xml_array['PubmedArticle']['MedlineCitation']['Article']['Pagination']['EndPage'];
// Yazar sayını ve yazar isimlerini bul	
if (isset ( $xml_array['PubmedArticle']['MedlineCitation']['Article']['AuthorList']['Author'][0])) {
// birden fazla yazar var
$n=0;
$count = count ($xml_array['PubmedArticle']['MedlineCitation']['Article']['AuthorList']['Author']);
for  ($i=0; $i<$count; $i++) {
	if (isset ($xml_array['PubmedArticle']['MedlineCitation']['Article']['AuthorList']['Author'][$i]['ForeName'] )) {
		// CollectiveName - Grup İsmi yok
		$author = $xml_array['PubmedArticle']['MedlineCitation']['Article']['AuthorList']['Author'][$i]; 
		$yazarlar = $yazarlar.$author['ForeName'].' '.$author['LastName'].', ';
		$n=$n+1;
		}
}
$yazarS=$n;
$yazarlar=substr ($yazarlar,0,-2);
}
else {
// tek yazar var	
	$yazarS=1;
	$yazarlar=$xml_array['PubmedArticle']['MedlineCitation']['Article']['AuthorList']['Author']['ForeName'].' '
	.$xml_array['PubmedArticle']['MedlineCitation']['Article']['AuthorList']['Author']['LastName'];
}

if (is_array ($xml_array['PubmedArticle']['MedlineCitation']['Article']['PublicationTypeList']['PublicationType'])) 
	$PublicationType=$xml_array['PubmedArticle']['MedlineCitation']['Article']['PublicationTypeList']['PublicationType'][0];
else $PublicationType=$xml_array['PubmedArticle']['MedlineCitation']['Article']['PublicationTypeList']['PublicationType'];

if (isset ($xml_array['PubmedArticle']['MedlineCitation']['Article']['Abstract'])) {
if (is_array ($xml_array['PubmedArticle']['MedlineCitation']['Article']['Abstract']['AbstractText']) )
	// Multi-line abstract, fetch only first line of abstract
	$AbstractText=$xml_array['PubmedArticle']['MedlineCitation']['Article']['Abstract']['AbstractText'][0];
else if (isset ($xml_array['PubmedArticle']['MedlineCitation']['Article']['Abstract']['AbstractText'])) 
	$AbstractText=$xml_array['PubmedArticle']['MedlineCitation']['Article']['Abstract']['AbstractText'];
}
// print_r ($xml_array['PubmedArticle']['MedlineCitation']);
}
}
?>
<a href="PMID nerede.png" target="_blank"> PMID nereden bakılır? </a>
<form method="post" action="">
pubmed id (PMID)  numarasını giriniz<br/>
<input type="text" name="pmid" id="pmid" value="<?php echo $PMID;?>" >
<input type="submit">
</form>
<button id="pubmedGoster" onclick="pubmedGoster()">Pubmed yayınını göster</button>
<button id="pubmedGetir" onclick="pubmedGetir()">Pubmed yayın bilgilerini JScript ile getir</button>
<br/>
PMID: <input type="text" name="PMID" size="19" maxlength="19" id="PMID" value="<?php echo $PMID;?>" >  
doi: <input type="text" name="doi" size="55"  id="doi" value="<?php echo $doi;?>"> <br/>
Makalenin başlığı: <input type="text" name="ArticleTitle" size="85"  id="ArticleTitle" value="<?php echo $ArticleTitle;?>"> <br/>
Dergi ismi: <input type="text" name="Title" size="50"  id="Title" value="<?php echo $dergi;?>"> 
Kısa ismi: <input type="text" name="ISOAbbreviation" size="26"  id="ISOAbbreviation" value="<?php echo $ISOAbbreviation;?>"> <br/>
ISSN: <input type="text" name="ISSN" size="8"  id="ISSN" value="<?php echo $ISSN;?>">
eISSN: <input type="text" name="eISSN" size="8"  id="eISSN" value="<?php echo $eISSN;?>"> <br/>
Yıl: <input type="text" name="Year" size="4"  id="Year" value="<?php echo $Year;?>">
Cilt: <input type="text" name="Volume" size="2"  id="Volume" value="<?php echo $Volume;?>">
Sayı: <input type="text" name="Issue" size="2"  id="Issue" value="<?php echo $Issue;?>">
Sayfa/numara: <input type="text" name="StartPage" size="2"  id="StartPage" value="<?php echo $StartPage;?>">
- <input type="text" name="EndPage" size="2"  id="EndPage" value="<?php echo $EndPage;?>">
Yazar sayısı: <input type="text" name="yazarS" size="2"  id="yazarS" value="<?php echo $yazarS;?>"><br/>
Yazarlar: <input type="text" name="yazarlar" size="95"  id="yazarlar" value="<?php echo $yazarlar;?>"><br/>
Yayın türü: <input type="text" name="PublicationType" size="20"  id="PublicationType" value="<?php echo $PublicationType;?>">
<br/>
Özet <br/>
<textarea rows = "20" cols = "90" name = "ozet" id="ozetAlan"><?php echo $AbstractText;?></textarea> 
<script>
function pubmedGoster() {
var	w=document.getElementById('pmid').value.replace(" ","");
	urlText = "https://pubmed.ncbi.nlm.nih.gov/"+w;
	window.open(urlText,"_blank");
}

async function pubmedGetir() {
var	w='https://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?db=pubmed&id='+
document.getElementById('pmid').value.replace(" ","");
// https://codetogo.io/how-to-fetch-xml-in-javascript/
fetch(w)
  .then(response => response.text())
  .then(data => {
    const parser = new DOMParser();
    const xmlDoc = parser.parseFromString(data, "application/xml");
    console.log(xmlDoc);
// delete values returned from php
document.getElementById('PMID').value="";
document.getElementById('doi').value="";
document.getElementById('ArticleTitle').value="";
document.getElementById('Title').value="";
document.getElementById('ISOAbbreviation').value="";
document.getElementById('ISSN').value="";
document.getElementById('eISSN').value="";
document.getElementById('Year').value="";
document.getElementById('Volume').value="";
document.getElementById('Issue').value="";
document.getElementById('StartPage').value="";
document.getElementById('EndPage').value="";
document.getElementById('yazarS').value="";
document.getElementById('yazarlar').value="";
document.getElementById('PublicationType').value="";
document.getElementById('ozetAlan').value="";

document.getElementById('PMID').value=xmlDoc.getElementsByTagName('PMID')[0].childNodes[0].nodeValue;
// Birden fazla id var sa bile, birincisini aldı
document.getElementById('doi').value=xmlDoc.getElementsByTagName('ELocationID')[0].childNodes[0].nodeValue;
// Makalenin başlığı
document.getElementById('ArticleTitle').value=xmlDoc.getElementsByTagName('ArticleTitle')[0].childNodes[0].nodeValue;
// Dergi ismi, " :" görürsen kes
var gereksiz=xmlDoc.getElementsByTagName('Title')[0].childNodes[0].nodeValue;
var gerekli = gereksiz.split(' :');
var dergi = gerekli[0];
document.getElementById('Title').value=dergi;
// Derginin kısa ismi
document.getElementById('ISOAbbreviation').value=xmlDoc.getElementsByTagName('ISOAbbreviation')[0].childNodes[0].nodeValue;
document.getElementById('ISSN').value=xmlDoc.getElementsByTagName('ISSNLinking')[0].childNodes[0].nodeValue;
document.getElementById('eISSN').value=xmlDoc.getElementsByTagName('ISSN')[0].childNodes[0].nodeValue;	
// Yıl, cilt, sayı
document.getElementById('Year').value=xmlDoc.getElementsByTagName('Year')[0].childNodes[0].nodeValue;	
if (xmlDoc.getElementsByTagName('Volume')[0])
	document.getElementById('Volume').value=xmlDoc.getElementsByTagName('Volume')[0].childNodes[0].nodeValue;	
if (xmlDoc.getElementsByTagName('Issue')[0])
	document.getElementById('Issue').value=xmlDoc.getElementsByTagName('Issue')[0].childNodes[0].nodeValue;	
	// Başlangıç-bitiş sayfası / numarası
if (xmlDoc.getElementsByTagName('StartPage')[0])
	document.getElementById('StartPage').value=xmlDoc.getElementsByTagName('StartPage')[0].childNodes[0].nodeValue;	
if (xmlDoc.getElementsByTagName('EndPage')[0])
	document.getElementById('EndPage').value=xmlDoc.getElementsByTagName('EndPage')[0].childNodes[0].nodeValue;	
if (xmlDoc.getElementsByTagName('PublicationType')[0])
	document.getElementById('PublicationType').value=xmlDoc.getElementsByTagName('PublicationType')[0].childNodes[0].nodeValue;	
if (xmlDoc.getElementsByTagName('AbstractText')[0])
	document.getElementById('ozetAlan').value=xmlDoc.getElementsByTagName('AbstractText')[0].childNodes[0].nodeValue;	
var yazarYaz='';
var yazarlar=xmlDoc.getElementsByTagName('AuthorList')[0];
var yazarSay=yazarlar.childNodes.length;
document.getElementById('yazarS').value=yazarSay;
for(var i=0; i<yazarSay;i++){
	if ( xmlDoc.getElementsByTagName('ForeName')[i]) {
    yazarYaz=yazarYaz+xmlDoc.getElementsByTagName('ForeName')[i].childNodes[0].nodeValue + ' ';
	yazarYaz=yazarYaz+xmlDoc.getElementsByTagName('LastName')[i].childNodes[0].nodeValue + ', '
	}
}
document.getElementById('yazarlar').value=yazarYaz.slice(0, -2); // remove ", " from the end
  })
  
  .catch(console.error);
}
</script>
</body>
</html>
