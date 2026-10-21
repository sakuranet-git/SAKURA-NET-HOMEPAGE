const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const path = require('node:path');
const source = fs.readFileSync(path.join(__dirname, '../posts/news_view.html'), 'utf8');
const fn = source.slice(source.indexOf('    function renderNewsText('), source.indexOf('  </script>', source.indexOf('    function renderNewsText(')));
const ctx = { URL };
vm.createContext(ctx);
vm.runInContext(fn, ctx);
const render = ctx.renderNewsText;
assert.equal(render('hello\nworld & <script>'), 'hello<br>world &amp; &lt;script&gt;');
assert.equal(render(''), '');
assert.match(render('https://www.php.net/index.php'), /href="https:\/\/www.php.net\/index.php"/);
assert.match(render('https://support.sakura.ad.jp/mainte/mainteentry.php?id=39972'), /\?id=39972/);
assert.match(render('https://example.com/?a=1&b=2'), /a=1&amp;b=2/);
assert.match(render('http://example.com'), /target="_blank" rel="noopener noreferrer"/);
assert.match(render('HTTPS://EXAMPLE.COM'), /href="https:\/\/example.com\/"/);
assert.equal((render('https://a.example/\nhttps://b.example/').match(/<a /g) || []).length, 2);
assert.equal((render('https://example.com/。次の文').match(/<a /g) || []).length, 1);
assert.match(render('(https://example.com/).'), /<\/a>\)\./);
for (const value of ['javascript:alert(1)', 'data:text/html,test', '//example.com', 'ftp://example.com', 'https://', 'https://user:password@example.com']) {
  assert.ok(!render(value).includes('<a '), value);
}
const attack = render('<img src=x onerror=alert(1)> https://example.com/" onclick="alert(1)');
assert.ok(!attack.includes('<img'));
assert.ok(!attack.includes(' onclick="'));
assert.ok(attack.includes('&quot;'));
assert.ok(!source.startsWith('\ufeff'));
console.log('PASS: URL links, legacy text/newlines, URL validation, injection escaping, UTF-8 BOM check');
