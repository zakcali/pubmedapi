<!DOCTYPE html>
<!-- pubmedapi V3.1: bu yazılım Dr. Zafer Akçalı tarafından oluşturulmuştur 
programmed by Zafer Akçalı, MD -->
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Pubmed numarasından makaleyi bul</title>
</head>

<body>
<?php
// pubmedapi
// By Zafer Akçalı, MD
// Zafer Akçalı tarafından programlanmıştır
require_once './getPmPublication.php';
$p=new getPmPublication ();

if (isset($_POST['pmid'])) {
$girilenveri=preg_replace("/[^0-9]/", "", $_POST["pmid"] ); // sadece rakamlar
if($girilenveri!="") 
	$p->pmPublication ($girilenveri);
}
?>
<a href="PMID nerede.png" target="_blank"> PMID nereden bakılır? </a>
<form method="post" action="">
pubmed id (PMID)  numarasını giriniz. <?php echo ' '.$p->dikkat;?><br/>
<input type="text" name="pmid" id="pmid" value="<?php echo $p->PMID;?>" >
<input type="submit" value="Pubmed yayın bilgilerini PHP ile getir"> 
</form>
<button id="pubmedGetir" onclick="pubmedGetir()">Pubmed yayın bilgilerini JScript ile getir</button>
<button id="pubmedGoster" onclick="pubmedGoster()">Pubmed yayınını göster</button>
<button id="doiGit" onclick="doiGit()">doi ile yayına git</button>
<br/>
PMID: <input type="text" name="PMID" size="19" maxlength="19" id="PMID" value="<?php echo $p->PMID;?>" >  
doi: <input type="text" name="doi" size="55"  id="doi" value="<?php echo $p->doi;?>"> <br/>
Makalenin başlığı: <input type="text" name="ArticleTitle" size="85"  id="ArticleTitle" value="<?php echo str_replace ('"',  '&#34',$p->ArticleTitle);?>"> <br/>
Dergi ismi: <input type="text" name="Title" size="50"  id="Title" value="<?php echo $p->dergi;?>"> 
Kısa ismi: <input type="text" name="ISOAbbreviation" size="26"  id="ISOAbbreviation" value="<?php echo $p->ISOAbbreviation;?>"> <br/>
ISSN: <input type="text" name="ISSN" size="8"  id="ISSN" value="<?php echo $p->ISSN;?>">
eISSN: <input type="text" name="eISSN" size="8"  id="eISSN" value="<?php echo $p->eISSN;?>"> <br/>
Yıl: <input type="text" name="Year" size="4"  id="Year" value="<?php echo $p->Year;?>">
Cilt: <input type="text" name="Volume" size="2"  id="Volume" value="<?php echo $p->Volume;?>">
Sayı: <input type="text" name="Issue" size="2"  id="Issue" value="<?php echo $p->Issue;?>">
Sayfa/numara: <input type="text" name="StartPage" size="2"  id="StartPage" value="<?php echo $p->StartPage;?>">
- <input type="text" name="EndPage" size="2"  id="EndPage" value="<?php echo $p->EndPage;?>">
Yazar sayısı: <input type="text" name="yazarS" size="2"  id="yazarS" value="<?php echo $p->yazarS;?>"><br/>
Yazarlar: <input type="text" name="yazarlar" size="95"  id="yazarlar" value="<?php echo $p->yazarlar;?>"><br/>
Yayın türü: <input type="text" name="PublicationType" size="20"  id="PublicationType" value="<?php echo $p->PublicationType;?>">
<br/>
Özet <br/>
<textarea rows = "20" cols = "90" name = "ozet" id="ozetAlan"><?php echo $p->AbstractText;?></textarea> 
<script>
function pubmedGoster() {
var	w=document.getElementById('pmid').value.replace(/\D/g, "");
	urlText = "https://pubmed.ncbi.nlm.nih.gov/"+w;
	window.open(urlText,"_blank");
}
function doiGit() {
var	w=document.getElementById('doi').value;
urlText = "https://doi.org/"+w;
if ( w != '')
	window.open(urlText,"_blank");
}
async function pubmedGetir() {
var	w='https://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?db=pubmed&id='+
document.getElementById('pmid').value.replace(/\D/g, ""); // Pubmedid için yazışan sadece rakamları al
// https://codetogo.io/how-to-fetch-xml-in-javascript/
fetch(w, { mode: 'cors'})
  .then(response => response.text())
  .then(data => {
    const parser = new DOMParser();
// console.log(data);
// özet metninden işaretleri kaldır: <b> </b> <sub> </sub>
const trimmed = data.replace(/<b>/g, "").replace(/<\/b>/g, "").replace(/<sub>/g, "").replace(/<\/sub>/g, ""); 
const xmlDoc = parser.parseFromString(trimmed, "application/xml");
// php ile çağrılmış ve doldurulmuş alanları sil
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
//PubmedId PMID
document.getElementById('PMID').value=xmlDoc.getElementsByTagName('PMID')[0].childNodes[0].nodeValue;
// eğer var ise, doi
if (xmlDoc.getElementsByTagName('ELocationID')[0]) {
var doi="";
const count=xmlDoc.getElementsByTagName('ELocationID').length;
for (var i=0; i<count; i++) {
	doi=xmlDoc.getElementsByTagName('ELocationID')[i].childNodes[0].nodeValue;
	if (doi.substring (0,3)== '10.') // gerçek doi, 10. ile başlar
		break;
	}
document.getElementById('doi').value=doi;
}
// Makalenin başlığı
document.getElementById('ArticleTitle').value=xmlDoc.getElementsByTagName('ArticleTitle')[0].childNodes[0].nodeValue;
// Dergi ismi, " :" görürsen sonrasını kes, kaldır-at
var gereksiz=xmlDoc.getElementsByTagName('Title')[0].childNodes[0].nodeValue;
var gerekli = gereksiz.split(' :');
var dergi = gerekli[0];
document.getElementById('Title').value=dergi;
// Derginin kısa ismi
document.getElementById('ISOAbbreviation').value=xmlDoc.getElementsByTagName('ISOAbbreviation')[0].childNodes[0].nodeValue;
// varsa ISSN numarası
if (xmlDoc.getElementsByTagName('ISSNLinking')[0])
	document.getElementById('ISSN').value=xmlDoc.getElementsByTagName('ISSNLinking')[0].childNodes[0].nodeValue;
// varsa eISSN numarası
if (xmlDoc.getElementsByTagName('ISSN')[0])
	document.getElementById('eISSN').value=xmlDoc.getElementsByTagName('ISSN')[0].childNodes[0].nodeValue;	
// Derginin basıldığı / yayımlandığı yıl : gördüğümüz ilk 'Year', maalesef makalenin basıldığı yıl olmayabilir, tam hedefi yazmak gerek
document.getElementById('Year').value=xmlDoc.evaluate('//PubmedArticle/MedlineCitation/Article/Journal/JournalIssue/PubDate/Year', xmlDoc).iterateNext().textContent;
// Eğer var ise, cilt numarası
if (xmlDoc.getElementsByTagName('Volume')[0])
	document.getElementById('Volume').value=xmlDoc.getElementsByTagName('Volume')[0].childNodes[0].nodeValue;	
// Eğer var ise sayı
if (xmlDoc.getElementsByTagName('Issue')[0])
	document.getElementById('Issue').value=xmlDoc.getElementsByTagName('Issue')[0].childNodes[0].nodeValue;	
// Eğer var ise, başlangıç sayfası veya elektronik dergilerde makale numarası
if (xmlDoc.getElementsByTagName('StartPage')[0])
	document.getElementById('StartPage').value=xmlDoc.getElementsByTagName('StartPage')[0].childNodes[0].nodeValue;	
// // Eğer var ise, bitiş sayfası
if (xmlDoc.getElementsByTagName('EndPage')[0])
	document.getElementById('EndPage').value=xmlDoc.getElementsByTagName('EndPage')[0].childNodes[0].nodeValue;	
// php'nin aksine, tek yazar için de, çok yazar için de aynı kod çalışıyor
var yazarYaz='';
var yazarSay=0;
const adlar = xmlDoc.evaluate('//PubmedArticle/MedlineCitation/Article/AuthorList/Author/ForeName', xmlDoc);
const soyadlar = xmlDoc.evaluate('//PubmedArticle/MedlineCitation/Article/AuthorList/Author/LastName', xmlDoc);
let buAd=adlar.iterateNext()
while (buAd) {
	ad=buAd.textContent 
	let soyad=soyadlar.iterateNext().textContent;
	yazarYaz=yazarYaz+ad + ' '+ soyad+ ', ';
	yazarSay=yazarSay+1;
	buAd=adlar.iterateNext();
}
// yazar sayısı: sadece Adı ve Soyadı olan gerçek insan isimleri sayıldı, yazar grubu ismi sayılmadı
document.getElementById('yazarS').value=yazarSay;
// yazarların isimleri. metin sonundaki boşluk ve virgül silindi
document.getElementById('yazarlar').value=yazarYaz.slice(0, -2); 
// yayın türü belirtilmiş ise al, birden fazla yayın türü var ise sadece ilk türü al
if (xmlDoc.getElementsByTagName('PublicationType')[0])
	document.getElementById('PublicationType').value=xmlDoc.getElementsByTagName('PublicationType')[0].childNodes[0].nodeValue;	
// Abstract, yani özet var ise al
if (xmlDoc.getElementsByTagName('AbstractText')[0])
	document.getElementById('ozetAlan').value=xmlDoc.getElementsByTagName('AbstractText')[0].childNodes[0].nodeValue;
  })
  .catch(console.error);
}
</script>
</body>
</html>
