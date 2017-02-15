create or replace view vw_users
as
select u.id id,
    u.name name,
    u.fullname fullname,
    sum(case q.status when "Approved" then 1 else 0 end) num_quotes,
    u.login login
  from users u
  join quotes q on q.speaker = u.name
  group by u.name
  order by 4;

create or replace view vw_quotes
as
select q.id id,
    q.quote quote,
    q.speaker name,
    u.fullname fullname,
    u.id speaker,
    q.context context,
    q.morestuff morestuff,
    q.date `date`,
    year(q.date) `year`,
    q.submittedby submittedby,
    q.createtime createtime,
    q.modifytime modifytime,
    q.status status,
    c.colour colour
  from quotes as q
  join users u on u.name = q.speaker
  join statuses c on c.status = q.status
  order by q.createtime;
