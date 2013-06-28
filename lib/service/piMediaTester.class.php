<?php
/**
* EN:
* Service allowing to test diem instance for nonexisting media references
* Checks if media references found in widgets and models are ok
* PL:
* Serwis umożliwiający wykonywanie testów spójności systemu pod względem istnienia mediów
* Sprawdza, czy media występujące w widgetach istnieją i są prawidłowe
*/
class piMediaTester
{
  
  function __construct()
  {
    $this->context = dmContext::getInstance();
    $this->loadModels();
    $this->report = new piMediaTestReport();
  }
  
  /**
   * EN:
   * Test suite
   * PL:
   * Zestaw testów
   * 
   * @return array wyniki testów
   * @author Jarek Rencz
   */
  public function test()
  {
    $this->testMediasInProjectModels(); 
    $this->testMediasInWidgetContent(); 
    $this->testMediasInWidgetMediaId();
    
    return $this->report;
    
    $this->testForContentImageWidgetsWithNoMediaAttached();
  }
  
  /**
   * EN:
   * Finds all occurences of Content Image widget without image
   * PL:
   * Odnajduje wszystkie wystąpienia Content Image bez zdefiniowanego obrazka
   *
   * @author Jarek Rencz
   */
  public function testForContentImageWidgetsWithNoMediaAttached()
  {
    $buffer = array();
    foreach($this->getContentImageWithNoMediaQuery()->fetchRecords() as $widget)
    {
      $buffer[] = $widget->id;
    }
    
    return $buffer;
  }
  
  /**
   * EN:
   * Finds all occurences of media in mediaId field and returns a report
   * PL:
   * Odnajduje wszystkie wystąpienia mediów w polu mediaId i zwraca raport
   *
   * @author Jarek Rencz
   */
  public function testMediasInWidgetMediaId()
  {
    $buffer = array();
    foreach($this->getMediaInWidgetMediaIdQuery()->fetchRecords() as $widget)
    {
      $values = $widget->getValues();
      $buffer[$values['mediaId']] = $widget->id;
    }
    
    $this->testMedias($buffer);
  }
  
  /**
   * EN:
   * Finds all occurences of media in widget text and returns a report
   * PL:
   * Odnajduje wszystkie wystąpienia mediów w treści widgeta i zwraca raport
   *
   * @author Jarek Rencz
   */
  public function testMediasInWidgetContent()
  {
    $buffer = array();
    foreach($this->getMediaInWidgetContentQuery()->fetchRecords() as $widget)
    {
      $values = $widget->getValues();
      preg_match_all('|media:(?P<mediaIds>\d+)*|', $values['text'], $arr, PREG_PATTERN_ORDER);
      foreach($arr['mediaIds'] as $id) 
      {
        $buffer[$id] = $widget->id;
      }   
    }
    
    $this->testMedias($buffer);
  }
  
  /**
   * EN:
   * Finds all occurences of media in markdown-enabled fields in all project models
   * PL:
   * Odnajduje wszystkie wystąpienia mediów w treści zapisanej
   * w polach typu markdown we wszystkich modelach projektu
   *
   * @param bool $ignoreHistory
   * @author Jarek Rencz
   */
  public function testMediasInProjectModels($ignoreHistory = true)
  {
    foreach ($this->getTestableEntities() as $entity) 
    {
      if ((substr($entity['model'], -7) === 'Version') && $ignoreHistory)
      {
        continue;
      }
      
      $buffer = array();
      foreach($this->getMediaInProjectModelColumnQuery($entity['model'], $entity['column'])->fetchRecords() as $record)
      {
        preg_match_all('|media:(?P<mediaIds>\d+)*|', $record->get($entity['column']), $arr, PREG_PATTERN_ORDER);
        foreach($arr['mediaIds'] as $id) 
        {
          $buffer[$id] = $record->id;
        }   
      }
      
      $this->testMedias($buffer, $entity['model'], $entity['column']);
    }
  }

  /**
   * EN:
   * Query searching for media in specified model column
   * PL:
   * Zapytanie odszukujące media w treści rekordów dla podanego modelu i kolumny
   *
   * @return Doctrine_Query
   * @author Jarek Rencz
   */  
  public function getMediaInProjectModelColumnQuery($model, $column)
  {
    return Doctrine_Core::getTable($model)
      ->createQuery()
      ->where('.' . $column . ' LIKE ?', '%](media:' . '%');
  }
  
  /**
   * EN:
   * Query serching media in widget content
   * PL:
   * Zapytanie odszukujące media w treści widgetów
   *
   * @return Doctrine_Query
   * @author Jarek Rencz
   */
  private function getMediaInWidgetContentQuery()
  {
    return DmWidgetTable::getInstance()
      ->createQuery('w')
      ->withI18n()
      ->where('wTranslation.value LIKE ?', '%](media:%');
  }
  
  /**
   * EN:
   * Query serching form media in mediaId field
   * PL:
   * Zapytanie odszukujące media w polu mediaId widgetów
   *
   * @return Doctrine_Query
   * @author Jarek Rencz
   */
  private function getMediaInWidgetMediaIdQuery()
  {
    return DmWidgetTable::getInstance()
      ->createQuery('w')
      ->withI18n()
      ->where('wTranslation.value LIKE ?', '%' . 'mediaId' . '%')
      ->andWhere('wTranslation.value NOT LIKE ?', '%' . 'mediaId":null' . '%');
  }
  
  /**
   * EN:
   * Query searching form Content Image widgets with no media attached
   * PL:
   * Zapytanie odszukujące widgety Content Image bez zdefiniowanego obrazka
   *
   * @return Doctrine_Query
   * @author Jarek Rencz
   */
  private function getContentImageWithNoMediaQuery()
  {
    return DmWidgetTable::getInstance()
      ->createQuery('w')
      ->withI18n()
      ->where('w.module = ?', 'dmWidgetContent')
      ->andWhere('w.action = ?', 'image')
      ->andWhere('wTranslation.value LIKE ?', '%' . 'mediaId":null' . '%');
    
  }
  
  /**
   * EN:
   * Runs tests to check if
   * * media record exists, 
   * * media record has file path defined and 
   * * file exists
   * PL:
   * Wykonuje testy pod kątem 
   * * istnienia rekordów mediów, 
   * * istnienia ścieżki w rekordzie i 
   * * istnienia pliku w ścieżce
   *
   * @param array $occurences 
   * @param string $modelName
   * @param string $column
   * @return void
   * @author Jarek Rencz
   */
  public function testMedias(array $occurences, $modelName = null, $column = null)
  {
    foreach($occurences as $mediaId => $entityId)
    {
      $reason = null;
      if ($media = DmMediaTable::getInstance()->find($mediaId))
      {
      
        if (!$media->get('file'))
        {
          $reason = piMediaTestReport::REASON_FILE_NOT_SET;
        }
        else if (!is_file($media->getFullPath()))
        {
          $reason = piMediaTestReport::REASON_FILE_MISSING;
        }
      }
      else
      {
        $reason = piMediaTestReport::REASON_NONEXISTING_MEDIA;
      }
      
      if ($modelName === null)
      {
        $this->report->registerWidget($mediaId, $entityId, $reason);
      }
      else
      {
        $this->report->registerRecord($mediaId, $entityId, $reason, $modelName, $column);
      }
    }
  }
  
  /**
   * EN:
   * Loads all model classes and returns an array of model names.
   * PL:
   * Ładuje wszystkie klasy modeli i zwraca jako tablicę
   * 
   * @return array An array of model names
   * @author Kris Wallsmith <kris.wallsmith@symfony-project.com>
   * @see    sfTaskExtraDoctrineBaseTask::loadModels()
   */
  private function loadModels()
  {
    Doctrine_Core::loadModels($this->context->getConfiguration()->getModelDirs());

    $this->models = Doctrine_Core::getLoadedModels();
    $this->models = Doctrine_Core::initializeModels($this->models);
    $this->models = Doctrine_Core::filterInvalidModels($this->models);

    return $this->models;
  }
  
  /**
   * EN:
   * Loads and returns models and columns which can contain media
   * PL:
   * Ładuje i zwraca kolumny i modele, które mają szansę zawierać media
   *
   * @return array tablica z parami model-kolumna
   * @author Jarek Rencz
   */
  public function getTestableEntities()
  {
    if (!isset($this->testableEntities))
    {
      foreach ($this->models as $modelName) {
        $modelTable = Doctrine_Core::getTable($modelName);
        foreach($modelTable->getAllColumnNames() as $column)
        {
          if($modelTable->isMarkdownColumn($column))
          {
            $this->testableEntities[] = array(
              'model' => $modelName,
              'column' => $column,  
            );
          }
        }
        unset($modelTable);
      }
    }
    return $this->testableEntities;
  }
  
}
