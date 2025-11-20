Table users {
  id id [pk, increment]
  name varchar(255)
  email varchar(255) [unique]
  nip varchar(255) [unique, null]
  phone varchar(15) [unique, null]
  photo varchar(255) [null]
  password varchar(255)
  role_id id [ref: > roles.id]
  active boolean
  created_at timestamp
}

Table roles {
  id id [pk, increment]
  name varchar(10)
  active boolean
}

Table permissions {
  id id [pk, increment]
  name varchar(255)
  label varchar(255)
}

Table permission_roles {
  role_id id [ref: > roles.id]
  permission_id id [ref: > permissions.id]
}

Table academic_years {
  id id [pk, increment]
  name varchar(255)
  semester enum(1,2)
  start_date date
  end_date date
  active boolean
  created_at timestamp
  updated_at timestamp
}

Table classes {
  id id [pk, increment]
  name varchar(255)
  teacher_id id [ref: > users.id, null]
  academic_year_id id [ref: > academic_years.id]
  active boolean
}

Table subjects {
  id id [pk, increment]
  name varchar(255)
  active boolean
}

Table students {
  id id [pk, increment]
  class_id id [ref: > classes.id]
  name varchar(255)
  gender enum("laki-laki", "perempuan")
  nis varchar(20)
  phone varchar(100)
  active boolean
}

Table student_class_histories {
  id id [pk, increment]
  class_id id [ref: > classes.id]
  student_id id [ref: > students.id]
  academic_year_id id [ref: > academic_years.id]
  start_date date [null]
  end_date date [null]
}

Table schedules {
  id id [pk, increment]
  teacher_id id [ref: > users.id]
  class_id id [ref: > classes.id]
  subject_id id [ref: > subjects.id]
  day enum("Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu")
  start_time time
  end_time time
  academic_year_id id [ref: > academic_years.id]
  active boolean
}

Table journals {
  id id [pk, increment]
  teacher_id id [ref: > users.id]
  class_id id [ref: > classes.id]
  academic_year_id id [ref: > academic_years.id]
  date date
  theme text [null]
  activity text [null]
  notes text [null]
  created_at timestamp
  updated_at timestamp
}

Table journal_subjects {
  id id [pk, increment]
  journal_id id [ref: > journals.id]
  subject_id id [ref: > subjects.id]
}

Table student_attendances {
  id id [pk, increment]
  date date
  teacher_id id [ref: > users.id]
  academic_year_id id [ref: > academic_years.id]
  created_at timestamp
}

Table student_attendance_subjects {
  id id [pk, increment]
  student_attendance_id id [ref: > student_attendances.id]
  subject_id id [ref: > subjects.id]
}

Table student_attendance_details {
  id id [pk, increment]
  student_attendance_id id [ref: > student_attendances.id]
  student_id id [ref: > students.id]
  status enum("hadir", "ijin", "sakit", "alpa", "telat")
  note text [null]
}

Table teacher_attendance {
  id id [pk, increment]
  teacher_id id [ref: > users.id]
  date date
  time_in time
  time_out time
  photo_in varchar(255)
  photo_out varchar(255)
}