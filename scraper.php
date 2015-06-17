<?php
require 'scraperwiki.php'; 

date_default_timezone_set('Australia/Sydney');
require 'scraperwiki/simple_html_dom.php';

$mainUrl = scraperWiki::scrape("http://www.fairfieldcity.nsw.gov.au/default.asp?iNavCatID=54&iSubCatID=2240");

$dom = new simple_html_dom();
$dom->load($mainUrl);
$container = $dom->find("#main table #content", 0);

foreach($container->find("table") as $table)
{
    $rows = $table->find("tr");
    print ("Number of records: " . sizeof($rows));
    for($i = 1; $i < sizeof($rows); $i++)
    {
      $row = $table->find("tr", $i);

      $record = array();

      $cell0 = $row->find("td", 0);
      $cell1 = $row->find("td", 1);
      $cell2 = $row->find("td", 2);
      $cell3 = $row->find("td", 3);
      $cell4 = $row->find("td", 4);

      $council_reference = trim(html_entity_decode($cell0->plaintext));
      $record['council_reference'] = preg_replace('/[ ]/', '', $council_reference);

      $record['address'] = trim(preg_replace('/[^a-zA-Z0-9 \.,]/', '', html_entity_decode($cell1->plaintext))) . ", NSW";

      $description = trim(html_entity_decode($cell2->plaintext));
      $description = preg_replace('/[^a-zA-Z0-9 \.,]/', '', $description);
      if(strlen($description) > 228)
      {
  	$description = substr($description, 0, 225) . "...";
      }
      $record['description'] = $description;

      $dateFromString = trim(html_entity_decode($cell3->plaintext));
      $dateFrom = date('Y-m-d', strtotime($dateFromString));
      if($dateFrom != '1970-01-01')
      {
  	$record['on_notice_from'] = $dateFrom;
      }

      $dateToString = trim(html_entity_decode($cell4->plaintext));
      $dateTo = date('Y-m-d', strtotime($dateToString));
      if($dateTo != '1970-01-01')
      {
  	$record['on_notice_to'] = $dateTo;
      }

      $record['info_url'] = 'http://www.fairfieldcity.nsw.gov.au/default.asp?iNavCatID=54&iSubCatID=2240';
      $record['comment_url'] = 'http://www.fairfieldcity.nsw.gov.au/default.asp?iDocID=6779&iNavCatID=54&iSubCatID=2249';
      $record['date_scraped'] = date('Y-m-d');

      if($record['council_reference'] != '' && $record['description'] != '')
      {
          $existingRecords = scraperwiki::select("* from data where `council_reference`='" . $record['council_reference'] . "'");
          if (count($existingRecords) == 0)
          {
              print ("Saving record " . $record['council_reference'] . "\n");
              //print_r ($record);
              scraperwiki::save($record['council_reference'], $record);
          }
          else
          {
              print ("Skipping already saved record " . $record['council_reference'] . "\n");
          }
      }
      else
      {
          print ("Unable to save the following record:\n");
          print_r ($record);
      }
    }
}

?>
