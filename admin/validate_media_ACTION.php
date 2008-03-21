<?php
#----------------------------------------------------------------------
#   Initialization and page contents.
#----------------------------------------------------------------------

require( '../_header.php' );

showUpdateSQL();
getBack();

require( '../_footer.php' );

#----------------------------------------------------------------------
function showUpdateSQL () {
#----------------------------------------------------------------------
  global $mediumId;

  foreach (getRawParamsThisShouldBeAnException() as $key => $value)
    if( preg_match( '/(accept|refuse|info|new|edit|save)(\d+)/', $key, $match )){
      $mediumId = $match[2];
      $func = $match[1] . 'Medium';
      $func();
  }
}

#----------------------------------------------------------------------
function refuseMedium () {
#----------------------------------------------------------------------
  global $mediumId;

  $command = "DELETE FROM CompetitionsMedia WHERE id='$mediumId'";
  dbCommand( $command );
  echo "I just did this : \n";
  echo $command;

}

#----------------------------------------------------------------------
function acceptMedium () {
#----------------------------------------------------------------------
  global $mediumId;

  $command = "UPDATE CompetitionsMedia SET status='accepted', timestampdecided=NOW() WHERE id=$mediumId";
  dbCommand( $command );
  echo "I just did this : \n";
  echo $command;

}

#----------------------------------------------------------------------
function infoMedium () {
#----------------------------------------------------------------------
  global $mediumId;

  $infos = dbQuery("
    SELECT media.*, competition.cellName
	 FROM CompetitionsMedia media, Competitions competition
	 WHERE media.id = '$mediumId'
	   AND competition.id = media.competitionId
  ");


  extract($infos[0]);

  echo "<table border='0' cellspacing='0' cellpadding='2' width='100%'>\n";
  
  $tableList = array (
    array ('Type', $type),
    array ('Competition', competitionLink( $competitionId, $cellName )),
    array ('Link', externalLink( $uri, $text )),
    array ('Submitter', emailLink( $submitterEmail, $submitterName )),
    array ('Comment', $submitterComment),
    array ('Submitted on:', $timestampSubmitted),
    array ('Decided on', $timestampDecided),
    array ('Status', $status)
  );

  foreach ($tableList as $table) {
    list($name, $value) = $table;
    echo "<tr><td>$name</td><td>$value</td></tr>";
  }
  echo "</table>";

  displayChoices( array(
    choiceButton( false, "refuse$mediumId", 'Erase' ),
    choiceButton( false, "accept$mediumId", 'Accept' )
  ));

}

#----------------------------------------------------------------------
function newMedium () {
#----------------------------------------------------------------------
  global $mediumId;

  dbCommand("INSERT INTO CompetitionsMedia (competitionId, status) VALUES ('WC1982', 'pending')");
  $id = dbQuery("SELECT LAST_INSERT_ID()");
  $mediumId = $id[0][0];
  
  editMedium();

}

#----------------------------------------------------------------------
function editMedium () {
#----------------------------------------------------------------------
  global $mediumId;

  $infos = dbQuery("
    SELECT *
	 FROM CompetitionsMedia
	 WHERE id = '$mediumId'
  ");


  extract($infos[0]);
  echo "<form method='POST' action='validate_media_ACTION.php'>\n";
  echo "<table border='0' cellspacing='0' cellpadding='2' width='100%'>\n";

  echo "<tr><td>Competition</td>";
  $optionsComp = "<td><select class='drop' id='competitionId' name='competitionId'>\n";
  foreach( getAllCompetitions() as $competition ) {
    $optionId = $competition['id'];
	 $optionName = $competition['cellName'];
    if ($optionId == $competitionId)
      $optionsComp .= "<option value='$optionId' selected='selected'>$optionName</option>\n";
    else
      $optionsComp .= "<option value='$optionId'>$optionName</option>\n";
  }
  $optionsComp .= "</select></td></tr>";
  
  echo $optionsComp;

  
  echo "<tr><td>Type</td>";
  echo "<td><select class='drop' id='type' name='type'>\n";
  foreach (array('article', 'report', 'multimedia') as $typeString)
    if ($type == $typeString)
      echo "<option value='$typeString' selected='selected'>$typeString</option>";
    else
      echo "<option value='$typeString'>$typeString</option>";


  $fieldList = array (
    array ('Text', 'text', $text),
    array ('Link', 'link', $uri),
    array ('Submitter Name', 'submitterName', $submitterName),
    array ('Submitter Email', 'submitterEmail', $submitterEmail),
    array ('Submitter Comment', 'submitterComment', $submitterComment)
  );


  foreach ($fieldList as $field) {
    list($title, $name, $value) = $field;
    echo "<tr><td>$title</td><td><input type='text' name='$name' value='$value' /></td></tr>\n";
  }

  echo "</table>";

  echo "<input type='submit' class='butt' value='Save' name='save$id' />";
  echo "<input type='submit' class='butt' value='Erase' name='refuse$id' />";

  echo "</form>";

}

#----------------------------------------------------------------------
function saveMedium () {
#----------------------------------------------------------------------
  global $mediumId;

  $type = getNormalParam( 'type' );
  $competitionId = getNormalParam( 'competitionId' );
  $text = getHtmlParam( 'text' );
  $link = getHtmlParam( 'link' );
  $submitterName = getHtmlParam( 'submitterName' );
  $submitterEmail = getHtmlParam( 'submitterEmail' );
  $submitterComment = getHtmlParam( 'submitterComment' );

  $command = "
  UPDATE CompetitionsMedia
    SET type='$type',
        competitionId='$competitionId',
        text='$text',
        uri='$link',
        submitterName='$submitterName',
        submitterEmail='$submitterEmail',
        submitterComment='$submitterComment'
    WHERE id=$mediumId";

  dbCommand( $command );
  echo "I just did this : \n";
  echo $command;

}



#----------------------------------------------------------------------
function getBack () {
#----------------------------------------------------------------------

  echo "<br /><br />I want to get <a href='validate_media.php?random=" . rand() . "'>back</a> !";

}



?>
