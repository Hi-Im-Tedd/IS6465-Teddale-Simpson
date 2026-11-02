Part 1

After submitting the form, my response was collected, validated and then the confirmation page displayed.

Part 2 

1. header.php provides the relevant header information consistently to every page. This includes required meta data, bootstrap code and the CSS link
2. nav.php provides the navigation bar code for every page.
3. footer.php provides the footer information that is displayed on every page.
4. require_once is appropriate because it ensures each included template is only loaded once. 
5. The CSS file is linked in the header.php file

Part 3

1. The textarea control is used because it is the best way to receive an input from the user that contains long strings of text. The type of value PHP receives is a String.
2. topics[] allows users to input multiple selections which are then stored in the array. Excellent for receiving one or more inputs form check boxes. 
3. prefferedtime allows the user to select one of the radio button options for time of day. PHP receives a string
4. preffereddate allows users to input a date value for the appointment they want to schedule. PHP also receives a string value of the date. 
5. form_name is a hidden control that is able to pass important information that can further validate submission. In this case a string of "tutoring_request" is passed.

Part 4

Valid Test

1. Values entered: Exam Review, Practice Problems; Evening; IS 6465; 11/05/2026; Front end development.
2. Request was accepted
3. The message displayed was that the request was received. Displayed some of the info inputed and a status of Pending review.
4. The business rule being checked was to validate: whether the date was in the past; the textarea value met the length requirements; if a valid course was inputted; if a time of day was selected; if the topic selected was a valid topic.

Missing or invalid value

1. Values entered: No topic; Evening; IS 6465; 12/03/2026; Front end development.
2. Request was rejected
3. The message displayed was that the request could not be submitted and let me know that I have to select at least one valid topic.
4. The business rule that failed validation was that at least one valid topic had to be selected.

Past Date

1. Values entered: Exam Review, Practice Problems; Evening; IS 6465; 09/02/2026; Front end development.
2. Request was rejected
3. The message displayed was that the request could not be submitted and let me know that preffered date cannot be in the past.
4. The business rule that failed validation was the preffered date

Part 5

CRUD Action         Page
Create              request-form.php
Read one request    request-details.php
Read all requests   requests.php
Update              edit-request.php
Delete              delete-request.php

Questions
1. The query parameter that appears is the request ID of the request I selected
2. GET is the appropriate query because we would just like to retrieve and display one of the stored requests
3. POST action should be used because an update is being made, meaning new information is being put into PHP and the database. The GET action would be used if trying to retrieve information.