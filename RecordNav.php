<?php
/**
 * REDCap External Module: Record Nav
 * Provides additional previous/next record navigation capabilities in the Data Collection section of the project page menu, and (optionally) on Record Home pages.
 * @author Luke Stevens, Murdoch Children's Research Institute
 */
namespace MCRI\RecordNav;

use ExternalModules\AbstractExternalModule;

class RecordNav extends AbstractExternalModule
{
    protected $project_id;
    protected $userDagId;
    protected $currentArm;
    protected $currentRecord;
    protected $previousRecord;
    protected $nextRecord;

    public function redcap_every_page_top($project_id) {
        global $Proj, $user_rights;
        if (empty($project_id) || empty($Proj) || empty($user_rights) || !isset($_GET['id'])) return;
        if (!$this->isREDCapPage('DataEntry/record_home.php')) return;
        
        $this->project_id = $project_id;
        $this->userDagId = $user_rights['group_id'];
        $this->currentArm = $this->escape($_GET['arm']) ?? 1;
        $this->currentRecord = $this->escape($_GET['id']);
        
        $this->setPreviousAndNext();
        $this->includeMenuNavButtons();
        if ($this->getProjectSetting('enable-rec-home-nav')) $this->includeRecHomeNavButtons();
    }

    public function redcap_data_entry_form_top($project_id, $record, $instrument, $event_id, $group_id, $repeat_instance) {
        global $Proj, $user_rights;
        if (empty($project_id) || empty($Proj) || empty($user_rights)) return;

        $this->project_id = $project_id;
        $this->userDagId = $user_rights['group_id'];
        $this->currentArm = $Proj->eventInfo[$event_id]['arm_num'];
        $this->currentRecord = $record;

        $this->setPreviousAndNext();
        $this->includeMenuNavButtons();
    }

    protected function setPreviousAndNext() {
        global $Proj, $table_pk, $order_id_by;

        //region from DataEntry::renderRecordHomePage()
        $study_id_array = \Records::getRecordList($this->project_id, $this->userDagId, true, false, $this->currentArm);
				
        // Custom record ordering is set
        if ($order_id_by != "" && $order_id_by != $table_pk)
        {
            $orderer_arr_getData = array();
            foreach (\Records::getData('array', $study_id_array, array($Proj->table_pk, $order_id_by), $Proj->firstEventId) as $this_record=>$event_data) {
                $orderer_arr_getData[$this_record] = $event_data[$Proj->firstEventId][$order_id_by];
            }
            natcasesort($orderer_arr_getData);
            $study_id_array = array_keys($orderer_arr_getData);
            unset($orderer_arr_getData);
        }
        //endregion

        $ids = array_keys($study_id_array);
        $indexCurrent = array_search($this->currentRecord, $ids);

        $this->previousRecord = ($indexCurrent > 0) ? $ids[$indexCurrent-1] : null;
        $this->nextRecord = ($indexCurrent < count($ids)) ? $ids[$indexCurrent+1] : null;
    }

    protected function includeMenuNavButtons() {
        $url = APP_PATH_WEBROOT.'DataEntry/record_home.php?pid='.$this->project_id.'&arm='.$this->currentArm.'&id=';
        $a = "<a class='RecordNav-MenuBtn btn btn-xs btn-outline fs12 mx-1 px-1 my-0 py-0' style='display:none;'";

        $prevSelector = 'RecordNav-MenuPrev';
        if (is_null($this->previousRecord)) {
            $prev = "<span id='$prevSelector'></span>";
        } else {
            $prevText = \RCView::tt_strip_tags('datatables_11').\RCView::tt_strip_tags('colon').' '; // Previous
            $prevIcon = "<i class='fas fa-chevron-left'></i>";
            $prevDisplay = $this->previousRecord;
            $prevUrl = $url.$this->previousRecord;
            $prev = "$a id='$prevSelector' title='$prevText $prevDisplay' href='$prevUrl'>$prevIcon</a>";
        }
        
        $nextSelector = 'RecordNav-MenuNext';
        if (is_null($this->nextRecord)) {
            $next = "<span id='$nextSelector'></span>";
        } else {
            $nextText = \RCView::tt_strip_tags('datatables_10').\RCView::tt_strip_tags('colon').' '; // Next
            $nextIcon = "<i class='fas fa-chevron-right'></i>";
            $nextDisplay = $this->nextRecord;
            $nextUrl = $url.$this->nextRecord;
            $next = "$a id='$nextSelector' title='$nextText $nextDisplay' href='$nextUrl'>$nextIcon</a>";
        }
        
        echo $prev;
        echo $next;
        ?>
        <script type='text/javascript'>
            $(document).ready(function(){
                $('#RecordNav-MenuPrev').insertBefore('#menuLnkChooseOtherRec').show();
                $('#RecordNav-MenuNext').insertAfter('#menuLnkChooseOtherRec').show();
            });
        </script>
        <?php
    }

    protected function includeRecHomeNavButtons() {
        $url = APP_PATH_WEBROOT.'DataEntry/record_home.php?pid='.$this->project_id.'&arm='.$this->currentArm.'&id=';
        $a = "<a class='RecordNav-RecHomeBtn nowrap btn btn-xs btn-outline fs12 mx-1 px-1 my-0 py-0'";
        
        $prevText = \RCView::tt_strip_tags('datatables_11').\RCView::tt_strip_tags('colon').' '; // Previous
        if (is_null($this->previousRecord)) {
            $prev = '';
        } else {
            $prevIcon = "<i class='fas fa-chevron-left'></i> ".$this->previousRecord;
            $prevDisplay = $this->previousRecord;
            $prevUrl = $url.$this->previousRecord;
            $prev = "$a id='RecordNav-RecHomePrev' title='$prevText $prevDisplay' href='$prevUrl'>$prevIcon</a>";
        }
        
        $nextText = \RCView::tt_strip_tags('datatables_10').\RCView::tt_strip_tags('colon').' '; // Next
        if (is_null($this->nextRecord)) {
            $next = '';
        } else {
            $nextIcon = $this->nextRecord." <i class='fas fa-chevron-right'></i>";
            $nextDisplay = $this->nextRecord;
            $nextUrl = $url.$this->nextRecord;
            $next = "$a id='RecordNav-RecHomeNext' title='$nextText $nextDisplay' href='$nextUrl'>$nextIcon</a>";
        }

        echo '<div id="RecordNav-RecHome" class="container mx-0 mb-2 p-0 d-none"><div class="row"><div class="col-6 p-0 text-left">'.$prev.'</div><div class="col-5 p-0 text-right">'.$next.'</div></div></div>';
        ?>
        <script type='text/javascript'>
            $(document).ready(function() {
                $('#RecordNav-RecHome')
                    .prependTo('#record_display_name')
                    .removeClass('d-none');
            });
        </script>
        <?php
    }
}