<?php
/**
* EN:
* Groups information about missing media and places where they occur
* PL:
* Grupuje informacje o brakujących mediach i miejscach gdzie występują
*/
class piMediaTestReport
{
  const REASON_NONEXISTING_MEDIA = 'media record does not exist';
  const REASON_FILE_MISSING      = 'media record exists, although media file is missing';
  const REASON_FILE_NOT_SET      = 'media record exists, but there\'s no path given';
  
  function __construct()
  {
    $this->missingMedia            = array();
    $this->widgetsWithMissingMedia = array();
    $this->recordsWithMissingMedia = array();
    
    $this->possibleReasons = array(
      self::REASON_NONEXISTING_MEDIA,
      self::REASON_FILE_MISSING,
      self::REASON_FILE_NOT_SET
    );
  }
  
  /**
   * EN:
   * Returns a report
   * PL:
   * Zwraca raport
   *
   * @return array tablica z raportami względem kolejnych kryteriów
   * @author Jarek Rencz
   */
  public function get()
  {
    return array(
      'missingMedia' => $this->getMissingMedia(),
      'widgetsWithMissingMedia' => $this->getWidgetsWithMissingMedia(),
      'recordsWithMissingMedia' => $this->getRecordsWithMissingMedia()
    );
  }
  
  public function getReason($mediaId)
  {
    return (array_key_exists($mediaId, $this->missingMedia)) ? $this->missingMedia[$mediaId] : '';
  }
  
  /**
   * EN:
   * Adds widget to missing media report
   * PL:
   * Dodanie do raportu widgetu z brakującymi mediami
   *
   * @param string $mediaId 
   * @param string $widgetId 
   * @param string $reason 
   * @return void
   * @author Jarek Rencz
   */
  public function registerWidget($mediaId, $widgetId, $reason)
  {
    if ($this->isValid($reason))
    {
      $this
        ->addMissingMedia($mediaId, $reason)
        ->addWidgetWithMissingMedia($mediaId, $widgetId);      
    }
  }
  
  /**
   * EN:
   * Adds record to missing media report 
   * PL:
   * Dodanie do raportu rekordu z brakującymi mediami
   *
   * @param int $mediaId 
   * @param int $recordId 
   * @param string $reason 
   * @param string $modelName 
   * @param string $column 
   * @return void
   * @author Jarek Rencz
   */
  public function registerRecord($mediaId, $recordId, $reason, $modelName, $column)
  {
    $this
      ->addMissingMedia($mediaId, $reason)
      ->addRecordWithMissingMedia($mediaId, $recordId, $modelName, $column);
  }
  
  /**
   * EN:
   * Returns part of report related to missing media
   * PL:
   * Zwraca fragment raportu dotyczący brakujących mediów
   *
   * @return array
   * @author Jarek Rencz
   */
  public function getMissingMedia()
  {
    return $this->missingMedia;
  }
  
  /**
   * EN:
   * Returns part of report related to widgets with missing media
   * PL:
   * Zwraca fragment raportu dotyczący widgetów z brakującymi mediami
   *
   * @return array
   * @author Jarek Rencz
   */
  public function getWidgetsWithMissingMedia()
  {
    return $this->widgetsWithMissingMedia;
  }
  
  /**
   * EN:
   * Returns part of report related to records with missing media
   * PL:
   * Zwraca fragment raportu dotyczący rekordów z brakującymi mediami
   *
   * @return array
   * @author Jarek Rencz
   */
  public function getRecordsWithMissingMedia()
  {
    return $this->recordsWithMissingMedia;
  }
  
  /**
   * EN:
   * Adds to missing media report
   * PL:
   * Dodaje do raportu brakujące media
   *
   * @return piMediaTestReport
   * @author Jarek Rencz
   */
  private function addMissingMedia($mediaId, $reason)
  {
    
    $this->missingMedia[$mediaId] = $reason;
    
    return $this;
  }
  
  /**
   * EN:
   * Adds widget with missing media to report
   * PL:
   * Dodaje do raportu widget z brakującymi mediami
   *
   * @return piMediaTestReport
   * @author Jarek Rencz
   */
  private function addWidgetWithMissingMedia($mediaId, $widgetId)
  {
    array_push($this->widgetsWithMissingMedia, array(
      'id'     => (int) $mediaId,
      'widget' => (int) $widgetId,  
    ));
    
    return $this;
  }
  
  /**
   * EN:
   * Adds record with missing media to report
   * PL:
   * Dodaje do raportu rekord z brakującymi mediami
   *
   * @return piMediaTestReport
   * @author Jarek Rencz
   */
  private function addRecordWithMissingMedia($mediaId, $recordId, $modelName, $column)
  {
    array_push($this->recordsWithMissingMedia, array(
      'id'     => (int) $mediaId,
      'model'  => $modelName, 
      'column' => $column,
      'record' => (int) $recordId, 
    ));
    
    return $this;
  }
  
  /**
   * EN:
   * Helper function checking if given reason is valid
   * PL: 
   * Helper sprawdzający, czy podany powód jest jednym ze znanych powodów
   *
   * @return bool
   * @author Jarek Rencz
   */
  private function isValid($reason)
  {
    return in_array($reason, $this->possibleReasons);
  }
}
