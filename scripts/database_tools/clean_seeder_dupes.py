import re

def clean_seeder(file_path):
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()

    # Find the parishes array content
    match = re.search(r'(\$parishes = \[)(.*?)(\s+\];)', content, re.DOTALL)
    if not match:
        print("Could not find parishes array")
        return

    prefix = match.group(1)
    array_content = match.group(2)
    suffix = match.group(3)

    # Extract individual entries like ['id' => X, ...]
    entries = re.findall(r"(\s+\[.*?\]),?", array_content)
    
    seen_ids = set()
    unique_entries = []
    
    for entry in entries:
        id_match = re.search(r"'id' => (\d+)", entry)
        if id_match:
            id_val = id_match.group(1)
            if id_val not in seen_ids:
                seen_ids.add(id_val)
                unique_entries.append(entry)
            else:
                print(f"Skipping duplicate ID: {id_val}")

    new_array_content = ",".join(unique_entries)
    new_content = content[:match.start()] + prefix + new_array_content + suffix + content[match.end():]

    with open(file_path, 'w', encoding='utf-8') as f:
        f.write(new_content)
    
    print(f"Cleaned {file_path}. Total unique parishes: {len(unique_entries)}")

clean_seeder('database/seeders/LocationSeeder.php')
