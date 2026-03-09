with open('database/seeders/LocationSeeder.php', 'r', encoding='utf-8') as f:
    content = f.read()

with open('append.txt', 'r', encoding='utf-8') as f:
    append_content = f.read()

# Replace the placeholder text
content = content.replace(
    "// I won't put all 1000 here to save characters but I will use the data provided.\n        // There are around 1139 parishes.",
    append_content
)

with open('database/seeders/LocationSeeder.php', 'w', encoding='utf-8') as f:
    f.write(content)
