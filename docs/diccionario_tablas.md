# Diccionario de Tablas de la Base de Datos (Inglés a Español)

Este archivo contiene la correspondencia de los nombres de las tablas y modelos de la base de datos, del inglés (idioma de la base de datos) al español (idioma de la interfaz/negocio).

| Tabla en BD (Inglés) | Nombre en Español | Descripción Breve |
| :----------------- | :---------------- | :---------------- |
| **Usuarios y Autenticación** | | |
| `users` | Usuarios | Cuentas de acceso al sistema |
| `roles` / `permissions` | Roles y Permisos | Niveles de acceso y permisos (Spatie) |
| **Ubicación Geográfica** | | |
| `states` | Estados | Entidades federales/departamentos |
| `municipalities` | Municipios | Divisiones de cada estado |
| `parishes` | Parroquias | Divisiones de cada municipio |
| **Estructura Organizacional** | | |
| `areas` | Áreas | Áreas de trabajo o departamentos |
| `positions` | Cargos | Posiciones de trabajo de los empleados |
| `assigned_posts` | Puestos Asignados | Relación de puestos por área |
| `units` | Unidades | Unidad de producción o trabajo |
| **Gestión de RRHH** | | |
| `employees` | Empleados | Personal de la granja |
| `veterinarians` | Veterinarios | Personal médico veterinario |
| `shifts` | Turnos | Horarios de trabajo |
| `contract_types` | Tipos de Contrato | Modalidad de contratación |
| `payroll_types` | Tipos de Nómina | Frecuencia de pago (semanal, etc.) |
| `attendances` | Asistencias | Registro de entradas y salidas |
| `attendance_statuses` | Estados de Asist. | Estatus (Presente, Ausente, Falta) |
| `employee_incidents` | Incidencias | Reportes o faltas de empleados |
| **Aprovisionamiento y Almacenes** | | |
| `warehouse_a002_s` | Almacén A002 | Inventario físico del almacén A002 |
| `warehouse_a006_s` | Almacén A006 | Inventario físico del almacén A006 |
| `transfer_requests` | Solicitudes de Transf.| Pedidos de traslado de insumos |
| **Configuraciones de Granja** | | |
| `barns` | Galpones | Edificios o naves de la granja |
| `barn_sections` | Secciones de Galpón | Subdivisiones dentro de los galpones |
| `pens` | Corrales | Divisiones físicas para alojar animales |
| `stages` | Etapas | Fases de vida (Maternidad, Destete, Engorde) |
| `death_systems` | Sistemas de Muerte | Categorización fisiológica de bajas |
| `death_causes` | Causas de Muerte | Motivos específicos de mortalidad |
| `death_types` | Tipos de Muerte | Entidad que define el tipo (muerte, eutanasia) |
| `animal_statuses` | Estados del Animal | Situación actual (Vivo, Muerto, Vendido) |
| **Producción y Trazabilidad** | | |
| `genetics` | Genéticas | Líneas genéticas de los cerdos |
| `births` | Nacimientos / Partos | Registro de partos |
| `birth_details` | Detalles de Parto | Lechones individuales nacidos |
| `animals` | Animales | Inventario individualizado de animales |
| `movements` | Movimientos | Historial de traslados, destetes o eventos |
| `certificates` | Certificados | Documentos y pedigrí (Certificados de origen) |
| **Sistema** | | |
| `module_usage` | Uso de Módulos | Trazabilidad de qué módulos se están usando |
