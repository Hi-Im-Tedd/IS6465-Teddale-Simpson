# Lab 7 Notes

## Part 1

1. The AI prompt used to generate to code for this lab was the same as Prof Chong's in the coding companion video "Generate code for all use cases UM-01 to UM-10 in the design document. Use skills pack docs (application builder, test-harness-generator, and traceability auditor. I provided lab6.zip containing the current application files and the current week reading handout as a reference." 

2. Directory Layout of Lab 7

lab7/
├── index.php
├── README.md
├── config/
│   └── db.php
├── css/
│   └── styles.css
├── database/
│   └── setup-database.sql
├── includes/
│   ├── auth.php              (NEW: session cookies, CSRF, requireLogin, requireRole)
│   ├── footer.php            (UPDATED)
│   ├── header.php            (UPDATED)
│   ├── nav.php               (UPDATED: role-aware nav + POST CSRF logout)
│   └── validation.php        (UPDATED: user & password validation helpers)
├── php/
│   ├── admin-user-delete.php (NEW: UM-08)
│   ├── admin-user-role.php   (NEW: UM-06)
│   ├── admin-user-status.php (NEW: UM-07)
│   ├── change-password.php   (NEW: UM-05)
│   ├── delete-request.php    (UPDATED: auth, role/ownership, CSRF)
│   ├── edit-request.php      (UPDATED: auth, role/ownership, CSRF)
│   ├── login.php             (NEW: UM-09)
│   ├── logout.php            (NEW: UM-10)
│   ├── manage-users.php      (NEW: UM-03)
│   ├── process-request.php   (UPDATED: auth, Student role, CSRF, user_id link)
│   ├── profile.php           (NEW: UM-02 & UM-04)
│   ├── register.php          (NEW: UM-01)
│   ├── request-details.php   (UPDATED: auth, role/ownership)
│   ├── request-form.php      (UPDATED: auth, Student role, read-only identity, CSRF)
│   ├── requests.php          (UPDATED: auth, Student own vs Admin all)
│   ├── router.php            (PRESERVED)
│   └── update-request.php    (UPDATED: auth, role/ownership, CSRF)
├── repositories/
│   ├── requestPDO.php        (UPDATED: ownership & admin queries)
│   ├── studentPDO.php        (UPDATED: user_id linking & profile sync)
│   └── userPDO.php           (NEW: CRUD & search SQL for users)
├── services/
│   ├── requestService.php    (UPDATED: role/ownership enforcement & user_id link)
│   └── userService.php       (NEW: UM-01 to UM-10 business rules & transactions)
└── tests/
    ├── integration-tests.php (UPDATED: full UM-01..UM-10 + UC01/UC02 regression suite)
    └── validation-tests.php  (PRESERVED)


3. Lab 7 extends on Lab 6 by further securing the Peer Tutoring Hub application. We have now implemented authentication and authorization while also making use of session cookies and CSRF tokens.
Some new functionality has also been added with users now being able to change their password, administrators being able to delete user accounts and also change account roles. Overall Lab 7 builds on the foundation of Lab 6 to deliver a more complete and secure application.

4. The newly generated admin-user-role.php was a newly generated php file that grants a user with the role of Administrator the ability to change the roles of other users. This code fulfills the requirement of UM-06 and WF-05. 
It also follows business rules by validating user roles, validating inputs and ensuring the use of CSRF tokens. 

## Part 2

1. sqlite3 tutoring.db < setup-database.sql

2. 

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

![alt text](<lab7/screenshots/successfulRegistration.png>)

![alt text](<lab7/screenshots/userProfile.png>)

![alt text](<lab7/screenshots/tutoringRequest.png>)


## Part 6

1. Authentication is verifying who the users claims to be. We do this by verifying the email address and password entered at the login screen.

2. Authorization is ensuring that the user only has access to what is restricted to that particular role. 

3. Student access is restricted to only that particular account. For example, a student can only see their own requests and edit make edits to only their profile and request. This is the opposite of Administrator access which allows administrators
broader controls, being able to see, edit and delete any and all requests and also see and make changes to all user accounts. This also includes changing account states and deleting accounts.  

4. The functions limited to Administrators are: Viewing and Editing All Requests, Viewing all Users and Managing User Accounts (i.e. Changing account role, changing account status or deleting an account).

5. The page remains at the login screen unless valid credentials are entered and verified. 

![alt text](<lab7/screenshots/manageUsers.png>)

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

## Part 9

