create or replace view vw_users
as
select u.id id,
    u.name name,
    u.fullname fullname,
    u.login login,
    b.id book_id,
    b.name book_name,
    ur.role_id role_id,
    r.role role,
    sum(case q.status when "Approved" then 1 else 0 end) num_quotes
  from users u
  left outer join user_roles ur on ur.user_id = u.id
  left outer join books b on b.id = ur.book_id
  left outer join roles r on r.id = ur.role_id
  left outer join quotes q on q.speaker_id = u.id and q.book_id = b.id
  group by 1, 2, 3, 4, 5, 6, 7, 8
  order by 9;
  
create or replace view vw_quotes
as
select q.id id,
	q.book_id book_id,
    b.name book_name,
    q.quote quote,
    q.speaker_id speaker_id,
    u1.name speaker_name,
    u1.fullname fullname,
    q.context context,
    q.morestuff morestuff,
    q.date `date`,
    year(q.date) `year`,
    q.submitter_id submitter_id,
    u2.name submitter_name,
    q.createtime createtime,
    q.modifytime modifytime,
    q.status status,
    c.colour colour
  from quotes as q
  join users u1 on u1.id = q.speaker_id
  join users u2 on u2.id = q.submitter_id
  join statuses c on c.status = q.status
  join books b on b.id = q.book_id
  order by q.createtime;
