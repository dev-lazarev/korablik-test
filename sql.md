SELECT c.name, count(o.id) as cnt
FROM clients c
left join orders o on c.id = o.clients_id
left join products p on o.id = p.order_id
where 
	p.id IN (151515,151617,151514) AND 
	FROM_UNIXTIME (ctime, '%m') = 3 AND
   	FROM_UNIXTIME (ctime, '%Y') = 2015
group by c.id
having cnt > 0
order by cnt desc;


SELECT c.*
FROM clients c
left join orders o on c.id = o.clients_id
where c.email LIKE '%@mail.ru' and o.id IS NULL;