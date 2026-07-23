import re

path = 'resources/views/honorarium.blade.php'
with open(path, 'r', encoding='utf-8') as f:
    content = f.read()

old = '<td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2"></td>'
new_nama = "<td class=\"border border-neutral-200 dark:border-neutral-700 px-2 py-2 font-medium text-neutral-900 dark:text-neutral-100\">{{ $row['nama'] }}</td>"

positions = [m.start() for m in re.finditer(re.escape(old), content)]

# Fix positions 15572 and 41766 (kepaniteraan, followed by jabatan context)
to_fix = [15572, 41766]

# Replace from highest to lowest to keep positions valid
for pos in sorted(to_fix, reverse=True):
    # Verify it's still matching at that position
    if content[pos:pos+len(old)] == old:
        content = content[:pos] + new_nama + content[pos+len(old):]
        print(f"Fixed position {pos}")
    else:
        print(f"Position {pos} shifted, searching nearby...")

with open(path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Done.")
