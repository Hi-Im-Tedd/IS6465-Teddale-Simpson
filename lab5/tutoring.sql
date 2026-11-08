CREATE TABLE if not exists students (
  student_id INTEGER PRIMARY KEY,
  student_name TEXT NOT NULL,
  student_email TEXT NOT NULL UNIQUE
);

CREATE TABLE if not exists tutoring_requests (
  request_id INTEGER PRIMARY KEY,
  student_id INTEGER NOT NULL,
  course TEXT NOT NULL,
  topic TEXT NOT NULL,
  preferred_date TEXT NOT NULL,
  preferred_time TEXT NOT NULL,
  status TEXT NOT NULL DEFAULT 'Open',
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES students(student_id)
);

.table
.schema

INSERT OR ignore INTO students (student_name, student_email)
VALUES ('Jordan Lee', 'jordan.lee@example.com');

INSERT OR ignore INTO students (student_name, student_email)
VALUES ('Avery Patel', 'avery.patel@example.com');

INSERT OR ignore INTO tutoring_requests
  (student_id, course, topic, preferred_date, preferred_time)
VALUES
  (1, 'IS 6465', 'Forms', '2026-11-15', '14:00'),
  (1, 'IS 6465', 'SQL SELECT', '2026-11-16', '14:00'),
  (2, 'IS 6465', 'SQLite CRUD', '2026-11-16', '16:00');


SELECT * FROM students;
SELECT * FROM tutoring_requests;

SELECT * FROM tutoring_requests
WHERE request_id = 2;

SELECT
  r.request_id,
  s.student_name,
  s.student_email,
  r.course, r.topic,
  r.preferred_date, r.preferred_time, r.status
FROM tutoring_requests AS r
JOIN students AS s ON r.student_id = s.student_id
ORDER BY r.preferred_date;

UPDATE tutoring_requests
SET topic = 'PHP arrays and forms'
WHERE request_id = 1;

UPDATE tutoring_requests
SET status = 'Cancelled'
WHERE request_id = 3;

INSERT OR ignore INTO tutoring_requests
  (student_id, course, topic, preferred_date, preferred_time)
VALUES
  (2, 'IS 6465', 'Delete Practice', '2026-11-25', '10:00');

DELETE from tutoring_requests
WHERE request_id = 4;