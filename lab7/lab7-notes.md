# Lab 7 Notes

## Part 1

1. The AI prompt used to generate to code for this lab was the same as Prof Chong's in the coding companion video "Generate code for all use cases UM-01 to UM-10 in the design document. Use skills pack docs (application builder, test-harness-generator, and traceability auditor. I provided lab6.zip containing the current application files and the current week reading handout as a reference." 

2. Directory Layout of Lab 7

![alt text](<screenshots/directoryLayout.png>)


3. Lab 7 extends on Lab 6 by further securing the Peer Tutoring Hub application. We have now implemented authentication and authorization while also making use of session cookies and CSRF tokens.
Some new functionality has also been added with users now being able to change their password, administrators being able to delete user accounts and also change account roles. Overall Lab 7 builds on the foundation of Lab 6 to deliver a more complete and secure application.

4. The newly generated admin-user-role.php was a newly generated php file that grants a user with the role of Administrator the ability to change the roles of other users. This code fulfills the requirement of UM-06 and WF-05. 
It also follows business rules by validating user roles, validating inputs and ensuring the use of CSRF tokens. 

## Part 2

1. sqlite3 tutoring.db < setup-database.sql

2. Running .tables while in the tutoring.db displays the three tables within the database

3. sqlite> .tables
students           tutoring_requests  users            
sqlite> 

## Part 3

1. In terminal we navigate to the database folder in Lab7 and run php create-admin.php

2. If the account is successfully created it returns "Active administrator account created" 

3. The email address used was tsimpson@school.edu

4. The role assigned to this account is Administrator

## Part 4

1. https://super-rotary-phone-g5xr6j5pxr73vp4v-8000.app.github.dev/php/login.php

2. The application opened up successfully without throwing any errors.

3. When first accessing the application I'm brought to the login screen where I am prompted for email address and password. I also have the ability to create a new account from this screen.


## Part 5

| Test | What I Did | Expected Result | Actual Result | Pass or Fail |
|---|---|---|---|---|
| Register student account | From login page, clicked create account and registered new student | Student account is created | Student account successfully created | Pass |
| Log in as student | Entered email address and password for newly registered student | Student can access protected student pages | Student is able access protected student pages | Pass |
| View or update profile | After successful login the student profile is displayed | Profile information is displayed or saved | Profile information was displayed | Pass |
| Create tutoring request | Clicked Request a Session on nav bar and entered valid request information | A new request is created with status Open | A new request was successfully created with a status of Open | Pass |
| Edit Open request | Clicked the edit button on the request details page and changed the request details | Updated request information is displayed | Updated request information was correctly displayed | Pass |
| View my requests | Clicked My Requests on the nav bar | Student sees the student's own requests | Student's own requests were displayed | Pass |
| Log out | Clicked the Log Out button | Session ends and login page appears | Session ended and the login page appears with a confirmation of successful logout | Pass |

![alt text](<screenshots/successfulRegistration.png>)

![alt text](<screenshots/userProfile.png>)

![alt text](<screenshots/tutoringRequest.png>)


## Part 6

1. Authentication is verifying who the users claims to be. We do this by verifying the email address and password entered at the login screen.

2. Authorization is ensuring that the user only has access to what is restricted to that particular role. 

3. Student access is restricted to only that particular account. For example, a student can only see their own requests and edit make edits to only their profile and request. This is the opposite of Administrator access which allows administrators
broader controls, being able to see, edit and delete any and all requests and also see and make changes to all user accounts. This also includes changing account states and deleting accounts.  

4. The functions limited to Administrators are: Viewing and Editing All Requests, Viewing all Users and Managing User Accounts (i.e. Changing account role, changing account status or deleting an account).

5. The page remains at the login screen unless valid credentials are entered and verified. 

![alt text](<screenshots/manageUsers.png>)

## Part 7

### Student Registation Workflow

1. register.php

2. userService.php

3. auth.php

4. userService.php calling validation functions from validation.php

5. userService.php

6. userPDO.php and studentPDO.php

7. The users and students tables

### User Login Workflow

1. login.php

2. login.php

3. user.PDO.php

4. userService.php

5. - A session ID is generated
   - Old CSRF token is removed and a new token is generated
   - Browsers is directed to the profile page

6. This prevents session fixation and allows the current session ID to be validated and preventing the old session from being re-used.

7. The role determination comes from querying the role column in the user table of the database. 

Password hashing and verification should occur in the application layer because this helps us properly separate responsibilities and also protects the presentation and database portions of our application.
Not putting it in the presentation layer prevents the process from being easily manipulated by any malicious actors. Not putting it in the database layer allows us to securely input the password information into the database using prepared statements which also protects our database from malicious actors.

## Part 8 

Using Gemini with the prompt stated above and the required documents provided. Only one new test file was generated, integration-tests.php. This test file tests various criteria that needs to be fulfilled to ensure all use cases in the design document are abiding by business rules. 
The validation-tests.php file was preseved as required. 

### Integration Tests

php tests/integration-tests.php\
Xdebug: [Step Debug] Could not connect to debugging client. Tried: localhost:9000 (through xdebug.client_host/xdebug.client_port) :-( \
PASS: UM-01: Student registration creates Active account\
PASS: UM-01: Password stored as one-way hash\
PASS: UM-01: Student registration reconciles students.user_id\
PASS: UM-01: Tutor registration creates Active Tutor without student record\
PASS: UM-01: Duplicate email rejected\
PASS: UM-01: Public Administrator registration denied\
PASS: UM-09: Valid login succeeds\
PASS: UM-09: Wrong password rejected\
PASS: UM-09: Unknown email rejected\
PASS: UM-04: Own name and email updated\
PASS: UM-04: Role and status immutable via profile edit\
PASS: UM-04: Linked student email synchronized\
PASS: UM-05: Incorrect current password rejected\
PASS: UM-05: Password changed with valid current password\
PASS: UM-05: Old password no longer authenticates\
PASS: UM-05: New password authenticates\
PASS: UM-03: Admin search by query, role, and status\
PASS: UM-03: Admin search returns empty array when no match\
PASS: UM-06: Non-admin role change denied\
PASS: UM-06: Demoting last active Administrator denied\
PASS: Ownership: Student sees only own tutoring requests\
PASS: Authorization: Administrator sees all tutoring requests\
PASS: Ownership: Student cannot view peer request\
PASS: Ownership: Student cannot edit peer request\
PASS: UM-07: Deactivating last active Administrator denied\
PASS: UM-07: Admin deactivates student account\
PASS: UM-07: Inactive user cannot log in\
PASS: UM-07: Admin reactivates student account\
PASS: UM-07: Reactivated user can log in\
PASS: UM-08: Deletion blocked when user has related tutoring requests\
PASS: UM-08: Blocked user account remains intact\
PASS: UM-08: Account with no related requests deleted\
PASS: UM-08: Deleted account removed from database

Warning: session_start(): Cannot send session cache limiter - headers already sent (output started at /workspaces/IS6465-Teddale-Simpson/lab7/tests/integration-tests.php:17) in /workspaces/IS6465-Teddale-Simpson/lab7/tests/integration-tests.php on line 199

Warning: session_start(): Cannot send session cache limiter - headers already sent (output started at /workspaces/IS6465-Teddale-Simpson/lab7/tests/integration-tests.php:17) in /workspaces/IS6465-Teddale-Simpson/lab7/includes/auth.php on line 17\
PASS: UM-10: Logout clears session data and destroys session\
Results: 34 passed, 0 failed

### Validation Tests

Valid time: PASS

Missing time: PASS

Altered value: PASS

Multiple valid topics: PASS

Missing topics: PASS

Altered topic: PASS

Minimum length: PASS

Maximum length: PASS

Too short: PASS

Too long: PASS

## Part 9

### Application Builder

One accurate statement from the AI is about the POST aspect of register.php. The page first invokes verifyCsrfToken(), comparing the submitted token with the session token using hash_equals.
It normalizes the displayed name, email, and role values and calls registerUser(). 

### Traceability Auditor

| Requirement | Presentation Page | Authentication or Authorization | Service or Validation | Repository or SQL | Database Table | Test Evidence |
|---|---|---|---|---|---|---|
| Student registers an account | `php/register.php` provides the full-name, email, password, confirmation, and Student/Tutor account fields. | `includes/auth.php` starts a secure session and `verifyCsrfToken()` validates the CSRF token. Public registration rejects the Administrator role in `registerUser()`. | `services/userService.php` normalizes and validates the input, checks duplicate email, hashes the password, starts a transaction, and creates a user. | `repositories/userPDO.php` contains `insertUser()` and its prepared `INSERT INTO users` statement. `repositories/studentPDO.php` provides `findOrCreateStudentForUser()`. | `users` stores the account and password hash; `students` stores the linked `user_id`. | `tests/integration-tests.php` passes UM-01 tests for Active student status, password hashing, student reconciliation, duplicate-email rejection, and public Administrator rejection. |
| Student logs in | `php/login.php` provides email and password fields and validates the POST with CSRF protection. | `authenticateUser()` in `services/userService.php` requires an Active account and verifies the password. `php/login.php` regenerates the session ID and stores `user_id`, `display_name`, and `role`; `includes/auth.php` protects subsequent pages with `requireLogin()` and `requireRole()`. | `authenticateUser()` normalizes email, calls `findUserByEmail()`, checks status and `password_verify()`, and rehashes when needed. | `repositories/userPDO.php` contains `findUserByEmail()` and `findUserById()` with prepared queries against `users`. | `users` supplies `password_hash`, `status`, and `role`; the SQLite database session is not a separate table. | `tests/integration-tests.php` passes UM-09 valid-login, wrong-password, and unknown-email checks. |
| Student creates a tutoring request | `php/request-form.php` provides course, date, reason, topics, and preferred-time fields. `php/process-request.php` receives the POST. | `request-form.php` calls `requireLogin()` and `requireRole()` to allow only Students. `process-request.php` repeats CSRF verification and the Student-role check. | `services/requestService.php` calls `validateSubmission()` and validates course, date, time, topics, and reason. `submitRequest()` links the request to the authenticated student and creates or finds the student record. | `repositories/requestPDO.php` contains `insertTutoringRequest()` and its prepared `INSERT INTO tutoring_requests` statement. `repositories/studentPDO.php` provides student lookup and creation. | `students.student_id` is the foreign key from `tutoring_requests.student_id`; the request stores course, JSON topic data, date, time, and the default `Open` status. | `tests/integration-tests.php` verifies two separate ownership-controlled requests and that a student cannot view or edit another student's request. `tests/validation-tests.php` passes preferred-time, topic, and reason boundary checks. |
| User logs out | The logout button in `includes/nav.php` submits a POST form to `php/logout.php`. | `logout.php` requires POST and verifies the CSRF token. `logoutSession()` in `includes/auth.php` clears `$_SESSION`, expires the session cookie, and calls `session_destroy()`. | `logoutSession()` is the authentication service operation that ends the user session. | No repository or SQL query is required for logout. | No database table is changed; logout affects the PHP session and cookie only. | `tests/integration-tests.php` passes UM-10: logout clears session data and destroys the session. |

### Test Harness Generator

1. The suggested test is already covered by the provided test files. 

2. To test manually one would log in as a particular user and create a tutoring request. Then, log out and log in as another user and check the list of requests, trying to find requests created by other users.

3. The AI generated test is appropriate for this application, which is why it has been covered by the Ownership: Student cannot view peer request and Ownership: Student cannot edit peer request checks in integration-tests.php.

The AI tool helped with understanding the tool by breaking down the workflow in detail and being able to target particular parts of code and expanding upon its function. Developers must verify AI generated explanations, code and tests against actual requirements to ensure
that generated code follows predetermined business rules, use case documents and design documents. This helps us ensure that the desired outcomes are being achieved. 

## Part 10

1. The LEFT JOIN connects the students table to the users table so that information can be pulled from both at once. 

2. The students table connects to the users table by using user_id from the users table as the foreign key in the students table.

3. This information is stored separately because not all users of the application are students.

4. An administrator would not have a matching row in the students table because the application validates the selected roles when a user is being registered and stops tutors and administrators from being inserted into the students table.