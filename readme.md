Nette/Esports Database
======================

@see [nette/database](https://github.com/nette/database)

Rozšíření API

Podmínka v LEFT JOIN
--------------------

Podmínka je připojena k LEFT JOIN klauzuli

``` ...->left() ```


```php
$selection = $context->table('book')
        ->left(':product_price.active', 1)
        ->select('book.*, :product_price.value');
```

```sql
SELECT book.*, product_price.value
FROM book
LEFT JOIN product_price ON book.id = product_price.book_id
AND (product_price.active = 1)
```
-------------------------------------

```php
$selection = $context->table('book')
		->left(':product_price.active', 1)
		->where(':book_tag.tag.name LIKE', 'PHP')
		->left(':product_price.value > ?', 0)
		->left(':book_tag.tag_id IS NOT NULL')
		->select('book.*, :product_price.value');
```

```sql
SELECT book.*, product_price.value FROM book
LEFT JOIN book_tag ON book.id = book_tag.book_id AND (book_tag.tag_id IS NOT NULL)
LEFT JOIN tag ON book_tag.tag_id = tag.id
LEFT JOIN product_price ON book.id = product_price.book_id AND (product_price.active = 1 AND product_price.value > 0)
WHERE (tag.name LIKE "PHP")
```
-------------------------------------
Podmínky lze libovolně míchat, jenom je třeba mít na paměti, že bude podmínka připojena k LEFT JOIN klauzuli podle prvního sloupečku v podmínce (obvykle je uveden jenom jeden, takže netřeba řešit).
```php
$selection = $context->table('book')
		->left(':product_price.active', 1)
		->left(':book_tag.tag_id IS NOT NULL OR :product_price.active IS NOT NULL') // bude pripojeno k book_tag
		->select('book.*, :product_price.value');
```
```sql
SELECT book.*, product_price.value FROM book
LEFT JOIN product_price ON book.id = product_price.book_id AND (product_price.active = 1)
LEFT JOIN book_tag ON book.id = book_tag.book_id AND (book_tag.tag_id IS NOT NULL OR product_price.active IS NOT NULL)

```
Aliasování
----------

``` ...->alias() ```

```php
$selection = $context
		->table('book')
		->alias(':product_price', 'pp')
		->left('pp.active', 1)
		->select('book.*, pp.value');
```
```sql
SELECT book.*, pp.value
FROM book
LEFT JOIN product_price AS pp ON book.id = pp.book_id AND (pp.active = 1)
```
-------------------------------------
```php
$selection = $context->table('book')
		->alias(':product_price', 'pp')
		->left('pp.active', 1)
		->alias(':book_tag.tag', 't')
		->where('t.name LIKE', 'PHP')
		->left('pp.value > ?', 0)
		->left(':book_tag.tag_id IS NOT NULL')
		->select('book.*, pp.value');
```
```sql
SELECT book.*, pp.value FROM book
LEFT JOIN book_tag ON book.id = book_tag.book_id AND (book_tag.tag_id IS NOT NULL)
LEFT JOIN tag AS t ON book_tag.tag_id = t.id
LEFT JOIN product_price AS pp ON book.id = pp.book_id AND (pp.active = 1 AND pp.value > 0)
WHERE (t.name LIKE "PHP")
```
-------------------------------------
LEFT JOIN s aliasem, ale vybira se z ne-aliasovane

```php
$selection = $context
		->table('book')
		->alias(':product_price', 'pp')
		->left('pp.active', 1)
		->select('book.*, :product_price.value');//tricky
```
```sql
SELECT book.*, product_price.value
FROM book
LEFT JOIN product_price AS pp ON book.id = pp.book_id AND (pp.active = 1)
LEFT JOIN product_price ON book.id = product_price.book_id
```

Vyřazení řetězce z vyhledávání
-------------------------------

Stává se, že v subselectu je třeba použít alias tabulky, a s ním pracovat. Klasická Selection se snaží tento alias dohledat, což končí chybou:

```php
...->where('author.id = (SELECT b.author_id FROM book AS b LIMIT 1)');
```

Řešením je použití vykřičníku před ```!b.author_id```, celé to vypadá:

```php
$selection = $context->table('author')
        ->where('author.id = (SELECT !b.author_id FROM book AS b LIMIT 1)');
```

```sql
SELECT *
FROM author
WHERE (author.id = (SELECT b.author_id FROM book AS b LIMIT 1))
```

Force index
-----------

Stejně jako v SQL: Zajistí použití požadovaného indexu.

``` ...->forceIndex() ```

```php
$selection = $context->table('book')
		->forceIndex('use_this_index')
		->select('book.*');
```

```sql
SELECT book.* FROM book
FORCE INDEX (`use_this_index`)
```
