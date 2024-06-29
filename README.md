Sofőr státusz:
- Söfőr becsekkol műszak elején: available
- Elfogad egy fuvart: not available. Plusz infó ETA
- Végez egy fuvarral: available

Egy külön set Redis -ben a státuszokkal.

User kér egy fuvart:
- Elérhető sofőrök lekérdezése
- Távolság meghatározása
- Legközelebbi sofőr küldése
- Ha nincs szabad sofőr akkor not available sofőr legkisebb ETA -val

Tesztek:
- User létre hoz egy "fuvar igényt"
- Ilyenkor értesíteni kell a sofőrt
- Sofőr elfogadja
- Felveszi
- Leadja

Mi történik ha egy fuvar accepted állapotban ragad

Mi történik ha épp nincs elérhető sofőr?
- X időközönként meg kell nézni hogy van -e elérhető sofőr
- Ha nincs akkor választani kell egy olyat aki épp "on-hold" és kicsi ETA

StateMachine?
