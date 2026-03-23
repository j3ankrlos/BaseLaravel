import re

def check_array(content, array_name):
    # Find the array content
    pattern = rf'\${array_name}\s*=\s*\[(.*?)(\s+\];)'
    match = re.search(pattern, content, re.DOTALL)
    if not match:
        print(f"Could not find {array_name}")
        return
    
    array_content = match.group(1)
    ids = re.findall(r"'id' => (\d+)", array_content)
    
    seen = {}
    dupes = []
    for i, id_val in enumerate(ids):
        if id_val in seen:
            dupes.append((id_val, seen[id_val], i))
        seen[id_val] = i
    
    if dupes:
        print(f"Duplicates in {array_name}: {dupes[:10]}")
    else:
        print(f"No duplicates in {array_name}")

content = open('database/seeders/LocationSeeder.php', 'r', encoding='utf-8').read()
check_array(content, 'states')
check_array(content, 'municipalities')
check_array(content, 'parishes')
