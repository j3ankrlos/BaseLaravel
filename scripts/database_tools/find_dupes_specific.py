import re
content = open('database/seeders/LocationSeeder.php', 'r', encoding='utf-8').read()

# Separate the file into parts
# Assuming they are roughly in order: States, Municipalities, Parishes
states_part = re.search(r"\$states = \[(.*?)\s+\];", content, re.DOTALL).group(1)
municipalities_part = re.search(r"\$municipalities = \[(.*?)\s+\];", content, re.DOTALL).group(1)
parishes_part = re.search(r"\$parishes = \[(.*?)\s+\];", content, re.DOTALL).group(1)

def find_duplicates(part, name):
    ids = re.findall(r"'id' => (\d+)", part)
    seen = set()
    dupes = []
    for i in ids:
        if i in seen:
            dupes.append(i)
        seen.add(i)
    if dupes:
        print(f"Duplicates in {name}: {dupes[:10]}...")
    else:
        print(f"No duplicates in {name}")

find_duplicates(states_part, "States")
find_duplicates(municipalities_part, "Municipalities")
find_duplicates(parishes_part, "Parishes")
