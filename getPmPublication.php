<?php
class getPmPublication {
	public $PMID='', $doi='', $ArticleTitle='', $dergi='', $ISOAbbreviation='', $ISSN='', $eISSN='', $Year='', $Volume='', $Issue='', $StartPage='', $EndPage='', $yazarlar='', $PublicationType='', $AbstractText='', $dikkat='';
	
	public $yazarS=0;
		    function __construct() {
		}
	final function pmPublication ($numara) {

	$preText="https://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?db=pubmed&id=";
	$url=$preText.preg_replace("/[^0-9]/", "", $numara ); // sadece rakamlar
// https://ncbiinsights.ncbi.nlm.nih.gov/2017/11/02/new-api-keys-for-the-e-utilities/
// saniyede 10'dan fazla sorgu için, api-key alarak aşağıdaki iki satırı açmalısınız: 
//$postText="";
	$postText="&api_key=your-API-KEY";
	$url = $url.$postText;
//echo ($url);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 15);
	curl_setopt($ch, CURLOPT_URL, $url);
	$data=curl_exec($ch);
	curl_close($ch);
	$xml_object = simplexml_load_string($data);
	$xml_array=json_decode(json_encode($xml_object),1);
// print_r ($xml_array['PubmedArticle']['MedlineCitation']);
// PMID gelmediyse hata var, gerisine devam etme
	if (!isset ($xml_array['PubmedArticle']['MedlineCitation']['PMID'])) {
		$this->dikkat='yayın bulunamadı';
		return; } 
// PMID geldi, devam et 		

//PubmedId PMID
	$this->PMID=($xml_array['PubmedArticle']['MedlineCitation']['PMID']);
// doi 
		if (isset ($xml_array['PubmedArticle']['MedlineCitation']['Article']['ELocationID'])) {
			if (!is_array ($xml_array['PubmedArticle']['MedlineCitation']['Article']['ELocationID']))
				$this->doi=($xml_array['PubmedArticle']['MedlineCitation']['Article']['ELocationID']);
			else {// dizi içinde 10.0 ile başlayan gerçek doi numarasını bulur 
				$count = count ($xml_array['PubmedArticle']['MedlineCitation']['Article']['ELocationID']);
				for  ($i=0; $i<$count; $i++) {
					$this->doi=$xml_array['PubmedArticle']['MedlineCitation']['Article']['ELocationID'][$i];
					if (substr ($this->doi, 0, 3) == '10.')
					break;
					}
				}
			}
// Makalenin başlığı
	$this->ArticleTitle= $xml_array['PubmedArticle']['MedlineCitation']['Article']['ArticleTitle'];
// Dergi ismi
	$this->dergi = $xml_array['PubmedArticle']['MedlineCitation']['Article']['Journal']['Title'];
	if (strpos($this->dergi, " :") !== false) // : kullanılmışsa gerisini kaldır at
		$this->dergi = substr($this->dergi, 0, strpos($this->dergi, " :"));
// Dergi kısa ismi
	$this->ISOAbbreviation= $xml_array['PubmedArticle']['MedlineCitation']['Article']['Journal']['ISOAbbreviation'];
// ISSN numarası
	if (isset ($xml_array['PubmedArticle']['MedlineCitation']['MedlineJournalInfo']['ISSNLinking']))
		$this->ISSN=$xml_array['PubmedArticle']['MedlineCitation']['MedlineJournalInfo']['ISSNLinking'];
//eISSN numarası
	if (isset ($xml_array['PubmedArticle']['MedlineCitation']['Article']['Journal']['ISSN']))
		$this->eISSN=$xml_array['PubmedArticle']['MedlineCitation']['Article']['Journal']['ISSN'];
// Derginin basıldığı / yayımlandığı yıl
	$this->Year =$xml_array['PubmedArticle']['MedlineCitation']['Article']['Journal']['JournalIssue']['PubDate']['Year'];
// Eğer var ise Cilt numarası
	if (isset ($xml_array['PubmedArticle']['MedlineCitation']['Article']['Journal']['JournalIssue']['Volume']) )
		$this->Volume=$xml_array['PubmedArticle']['MedlineCitation']['Article']['Journal']['JournalIssue']['Volume'];
//Sayı
	if (isset ($xml_array['PubmedArticle']['MedlineCitation']['Article']['Journal']['JournalIssue']['Issue']))
		$this->Issue= $xml_array['PubmedArticle']['MedlineCitation']['Article']['Journal']['JournalIssue']['Issue'];
// Başlangıç sayfası veya elektronik dergilerde makale numarası
	if (isset ($xml_array['PubmedArticle']['MedlineCitation']['Article']['Pagination']['StartPage']))
		$this->StartPage= $xml_array['PubmedArticle']['MedlineCitation']['Article']['Pagination']['StartPage'];
// Bitiş sayfası
	if (isset($xml_array['PubmedArticle']['MedlineCitation']['Article']['Pagination']['EndPage']))
		$this->EndPage= $xml_array['PubmedArticle']['MedlineCitation']['Article']['Pagination']['EndPage'];
// Yazar sayısını ve yazar isimlerini bul	
	if (isset ( $xml_array['PubmedArticle']['MedlineCitation']['Article']['AuthorList']['Author'][0])) {
// birden fazla yazar var: hepsini teker teker çağır, sadece isim-soyismi olan yazarları topla, grup ismi var ise sayma
		$n=0;
		$count = count ($xml_array['PubmedArticle']['MedlineCitation']['Article']['AuthorList']['Author']);
		for  ($i=0; $i<$count; $i++) {
			if (isset ($xml_array['PubmedArticle']['MedlineCitation']['Article']['AuthorList']['Author'][$i]['ForeName'] )) {
		// CollectiveName - Grup İsmi yok
			$author = $xml_array['PubmedArticle']['MedlineCitation']['Article']['AuthorList']['Author'][$i]; 
			$this->yazarlar = $this->yazarlar.$author['ForeName'].' '.$author['LastName'].', ';
			$n=$n+1;
			}
		}
// yazar sayısı
	$this->yazarS=$n;
// yazarların isimleri. metin sonundaki boşluk ve virgül silindi
	$this->yazarlar=substr ($this->yazarlar,0,-2);
		} else {
// tek yazar var, yazar sayısı 1
		$this->yazarS=1;
// tek yazarın ismi
		$this->yazarlar=$xml_array['PubmedArticle']['MedlineCitation']['Article']['AuthorList']['Author']['ForeName'].' '.$xml_array['PubmedArticle']['MedlineCitation']['Article']['AuthorList']['Author']['LastName'];
}
// yayın türü belirtilmiş ise ve birden fazla yayın türü var: sadece ilk türü al
	if (is_array ($xml_array['PubmedArticle']['MedlineCitation']['Article']['PublicationTypeList']['PublicationType'])) 
		$this->PublicationType=$xml_array['PubmedArticle']['MedlineCitation']['Article']['PublicationTypeList']['PublicationType'][0];
// yayın türü belirtilmiş, ama sadece 1 adet yayın türü var	
	else $this->PublicationType=$xml_array['PubmedArticle']['MedlineCitation']['Article']['PublicationTypeList']['PublicationType'];
// Abstract, yani özet var ise
	if (isset ($xml_array['PubmedArticle']['MedlineCitation']['Article']['Abstract'])) {
// Özet, her cümlesi ayrı bir dizi elemanı olacak şekilde dizilmiş ise, dizinin sadece ilk elemanını al
		if (is_array ($xml_array['PubmedArticle']['MedlineCitation']['Article']['Abstract']['AbstractText']) )
			$this->AbstractText=$xml_array['PubmedArticle']['MedlineCitation']['Article']['Abstract']['AbstractText'][0];
// özet, çok cümleli de olsa, tek bir eleman olarak aktarılmış
		else if (isset ($xml_array['PubmedArticle']['MedlineCitation']['Article']['Abstract']['AbstractText'])) 
			$this->AbstractText=$xml_array['PubmedArticle']['MedlineCitation']['Article']['Abstract']['AbstractText'];
		
		} 
	} // final function pmPublication
}
