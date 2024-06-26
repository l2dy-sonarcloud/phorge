<?php

final class DifferentialResponsibleViewerFunctionDatasource
  extends PhabricatorTypeaheadDatasource {

  public function getBrowseTitle() {
    return pht('Browse Viewer');
  }

  public function getPlaceholderText() {
    return pht('Type viewer()...');
  }

  public function getDatasourceApplicationClass() {
    return PhabricatorPeopleApplication::class;
  }

  public function getDatasourceFunctions() {
    return array(
      'viewer' => array(
        'name' => pht('Current Viewer'),
        'summary' => pht('Use the current viewing user.'),
        'description' => pht(
          'Show revisions the current viewer is responsible for. This '.
          'function includes revisions the viewer is responsible for through '.
          'membership in projects and packages.'),
      ),
    );
  }

  protected function isFunctionWithLoginRequired($function) {
    return true;
  }

  public function loadResults() {
    if ($this->getViewer()->isLoggedIn()) {
      $results = array($this->renderViewerFunctionToken());
    } else {
      $results = array();
    }

    return $this->filterResultsAgainstTokens($results);
  }

  protected function canEvaluateFunction($function) {
    if (!$this->getViewer()->isLoggedIn()) {
      return false;
    }

    return parent::canEvaluateFunction($function);
  }

  protected function evaluateFunction($function, array $argv_list) {
    $results = array();
    foreach ($argv_list as $argv) {
      $results[] = $this->getViewer()->getPHID();
    }

    return DifferentialResponsibleDatasource::expandResponsibleUsers(
      $this->getViewer(),
      $results);
  }

  public function renderFunctionTokens($function, array $argv_list) {
    $tokens = array();
    foreach ($argv_list as $argv) {
      $tokens[] = PhabricatorTypeaheadTokenView::newFromTypeaheadResult(
        $this->renderViewerFunctionToken());
    }
    return $tokens;
  }

  private function renderViewerFunctionToken() {
    return $this->newFunctionResult()
      ->setName(pht('Current Viewer'))
      ->setPHID('viewer()')
      ->setIcon('fa-user')
      ->setUnique(true);
  }

}
