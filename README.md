********************************************************************************
# Record Navigation: Previous and Next Record

Luke Stevens, Murdoch Children's Research Institute https://www.mcri.edu.au

[https://github.com/lsgs/redcap-record-nav/](https://github.com/lsgs/redcap-record-nav/)
********************************************************************************
## Summary of Functionality

Provides additional previous/next record navigation capabilities in the Data Collection section of the project page menu, and (optionally) on Record Home pages.

<img alt="Record Navigation: Previous and Next" src="https://redcap.mcri.edu.au/surveys/index.php?pid=14961&__passthru=DataEntry%2Fimage_view.php&doc_id_hash=ef2ab0d3a31ed3a29cdbdd2a1cd26fc1f2876363&id=2110596&s=5yHV6MDyPiWFNJJJ&page=file_page&record=11&event_id=47634&field_name=thefile&instance=1" />

### Data Entry Form

Adds `<` and `>` buttons either side of the "Select other record" link in the left-hand menu.

### Record Home Page (Optional)

Adds `<` and `>` buttons either side of the displayed record ID.

## Behaviour

* Navigation will occur only within the current record's arm.
* Navigation is *to* the Record Home page of the previous/next record.
* The sequence of the record id values will match the sequence that records are displayed in the select list on the Add/Edit Records page. (In non-longitudinal projects the Additional Functionality dialog contains an option for sorting according to values in a selected field.)
* The `<` (previous record) navigation button will not be displayed when viewing the first record.
* The `>` (next record) navigation button will not be displayed when viewing the last record.

## Configuration

The addition of "Previous"/"Next" buttons above the current record ID on the Record Home page must be enabled via the project module settings. It is disabled by default.

********************************************************************************