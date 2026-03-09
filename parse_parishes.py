import json

# The raw data
parishes_data = """IdParroquia	FK_IdMunicipio	Parroquia
1	1	Alto Orinoco
2	1	Huachamacare Acanaña
3	1	Marawaka Toky Shamanaña
4	1	Mavaka Mavaka
5	1	Sierra Parima Parimabé
6	2	Ucata Laja Lisa
7	2	Yapacana Macuruco
8	2	Caname Guarinuma
9	3	Fernando Girón Tovar
10	3	Luis Alberto Gómez
11	3	Pahueña Limón de Parhueña
12	3	Platanillal Platanillal
13	4	Samariapo
14	4	Sipapo
15	4	Munduapo
16	4	Guayapo
17	5	Alto Ventuari
18	5	Medio Ventuari
19	5	Bajo Ventuari
20	6	Victorino
21	6	Comunidad
22	7	Casiquiare
23	7	Cocuy
24	7	San Carlos de Río Negro
25	7	Solano
26	8	Anaco
27	8	San Joaquín
28	9	Cachipo
29	9	Aragua de Barcelona
30	10	Lechería
31	10	El Morro
32	11	Puerto Píritu
33	11	San Miguel
34	11	Sucre
35	12	Valle de Guanape
36	12	Santa Bárbara
37	13	El Chaparro
38	13	Tomás Alfaro
39	13	Calatrava
40	14	Guanta
41	14	Chorrerón
42	15	Mamo
43	15	Soledad
44	16	Mapire
45	16	Piar
46	16	Santa Clara
47	16	San Diego de Cabrutica
48	16	Uverito
49	16	Zuata
50	17	Puerto La Cruz
51	17	Pozuelos
52	18	Onoto
53	18	San Pablo
54	19	San Mateo
55	19	El Carito
56	19	Santa Inés
57	19	La Romereña
58	20	Atapirire
59	20	Boca del Pao
60	20	El Pao
61	20	Pariaguán
62	21	Cantaura
63	21	Libertador
64	21	Santa Rosa
65	21	Urica
66	22	Píritu
67	22	San Francisco
68	23	San José de Guanipa
69	24	Boca de Uchire
70	24	Boca de Chávez
71	25	Pueblo Nuevo
72	25	Santa Ana
73	26	Bergantín
74	26	Caigua
75	26	El Carmen
76	26	El Pilar
77	26	Naricual
78	26	San Crsitóbal
79	27	Edmundo Barrios
80	27	Miguel Otero Silva
81	28	Achaguas
82	28	Apurito
83	28	El Yagual
84	28	Guachara
85	28	Mucuritas
86	28	Queseras del medio
87	29	Biruaca
88	30	Bruzual
89	30	Mantecal
90	30	Quintero
91	30	Rincón Hondo
92	30	San Vicente
93	31	Guasdualito
94	31	Aramendi
95	31	El Amparo
96	31	San Camilo
97	31	Urdaneta
98	32	San Juan de Payara
99	32	Codazzi
100	32	Cunaviche
101	33	Elorza
102	33	La Trinidad
103	34	San Fernando
104	34	El Recreo
105	34	Peñalver
106	34	San Rafael de Atamaica
107	35	Pedro José Ovalles
108	35	Joaquín Crespo
109	35	José Casanova Godoy
110	35	Madre María de San José
111	35	Andrés Eloy Blanco
112	35	Los Tacarigua
113	35	Las Delicias
114	35	Choroní
115	36	Bolívar
116	37	Camatagua
117	37	Carmen de Cura
118	38	Santa Rita
119	38	Francisco de Miranda
120	38	Moseñor Feliciano González
121	39	Santa Cruz
122	40	José Félix Ribas
123	40	Castor Nieves Ríos
124	40	Las Guacamayas
125	40	Pao de Zárate
126	40	Zuata
127	41	José Rafael Revenga
128	42	Palo Negro
129	42	San Martín de Porres
130	43	El Limón
131	43	Caña de Azúcar
132	44	Ocumare de la Costa
133	45	San Casimiro
134	45	Güiripa
135	45	Ollas de Caramacate
136	45	Valle Morín
137	46	San Sebastían
138	47	Turmero
139	47	Arevalo Aponte
140	47	Chuao
141	47	Samán de Güere
142	47	Alfredo Pacheco Miranda
143	48	Santos Michelena
144	48	Tiara
145	49	Cagua
146	49	Bella Vista
147	50	Tovar
148	51	Urdaneta
149	51	Las Peñitas
150	51	San Francisco de Cara
151	51	Taguay
152	52	Zamora
153	52	Magdaleno
154	52	San Francisco de Asís
155	52	Valles de Tucutunemo
156	52	Augusto Mijares
157	53	Sabaneta
158	53	Juan Antonio Rodríguez Domínguez
159	54	El Cantón
160	54	Santa Cruz de Guacas
161	54	Puerto Vivas
162	55	Ticoporo
163	55	Nicolás Pulido
164	55	Andrés Bello
165	56	Arismendi
166	56	Guadarrama
167	56	La Unión
168	56	San Antonio
169	57	Barinas
170	57	Alberto Arvelo Larriva
171	57	San Silvestre
172	57	Santa Inés
173	57	Santa Lucía
174	57	Torumos
175	57	El Carmen
176	57	Rómulo Betancourt
177	57	Corazón de Jesús
178	57	Ramón Ignacio Méndez
179	57	Alto Barinas
180	57	Manuel Palacio Fajardo
181	57	Juan Antonio Rodríguez Domínguez
182	57	Dominga Ortiz de Páez
183	36	Barinitas
184	36	Altamira de Cáceres
185	36	Calderas
186	59	Barrancas
187	59	El Socorro
188	59	Mazparrito
189	60	Santa Bárbara
190	60	Pedro Briceño Méndez
191	60	Ramón Ignacio Méndez
192	60	José Ignacio del Pumar
193	61	Obispos
194	61	Guasimitos
195	61	El Real
196	61	La Luz
197	62	Ciudad Bolívia
198	62	José Ignacio Briceño
199	62	José Félix Ribas
200	62	Páez
201	63	Libertad
202	63	Dolores
203	63	Santa Rosa
204	63	Palacio Fajardo
205	64	Ciudad de Nutrias
206	64	El Regalo
207	64	Puerto Nutrias
208	64	Santa Catalina
209	65	Cachamay
210	65	Chirica
211	65	Dalla Costa
212	65	Once de Abril
213	65	Simón Bolívar
214	65	Unare
215	65	Universidad
216	65	Vista al Sol
217	65	Pozo Verde
218	65	Yocoima
219	65	5 de Julio
220	66	Cedeño
221	66	Altagracia
222	66	Ascensión Farreras
223	66	Guaniamo
224	66	La Urbana
225	66	Pijiguaos
226	67	El Callao
227	68	Gran Sabana
228	68	Ikabarú
229	69	Catedral
230	69	Zea
231	69	Orinoco
232	69	José Antonio Páez
233	69	Marhuanta
234	69	Agua Salada
235	69	Vista Hermosa
236	69	La Sabanita
237	69	Panapana
238	70	Andrés Eloy Blanco
239	70	Pedro Cova
240	71	Raúl Leoni
241	71	Barceloneta
242	71	Santa Bárbara
243	71	San Francisco
244	72	Roscio
245	72	Salóm
246	73	Sifontes
247	73	Dalla Costa
248	73	San Isidro
249	49	Sucre
250	49	Aripao
251	49	Guarataro
252	49	Las Majadas
253	49	Moitaco
254	75	Padre Pedro Chien
255	75	Río Grande
256	76	Bejuma
257	76	Canoabo
258	76	Simón Bolívar
259	77	Güigüe
260	77	Carabobo
261	77	Tacarigua
262	78	Mariara
263	78	Aguas Calientes
264	79	Ciudad Alianza
265	79	Guacara
266	79	Yagua
267	80	Morón
268	80	Yagua
269	42	Tocuyito
270	42	Independencia
271	82	Los Guayos
272	83	Miranda
273	84	Montalbán
274	85	Naguanagua
275	86	Bartolomé Salóm
276	86	Democracia
277	86	Fraternidad
278	86	Goaigoaza
279	86	Juan José Flores
280	86	Unión
281	86	Borburata
282	86	Patanemo
283	87	San Diego
284	88	San Joaquín
285	89	Candelaria
286	89	Catedral
287	89	El Socorro
288	89	Miguel Peña
289	89	Rafael Urdaneta
290	89	San Blas
291	89	San José
292	89	Santa Rosa
293	89	Negro Primero
294	90	Cojedes
295	90	Juan de Mata Suárez
296	91	Tinaquillo
297	92	El Baúl
298	92	Sucre
299	93	La Aguadita
300	93	Macapo
301	94	El Pao
302	95	El Amparo
303	95	Libertad de Cojedes
304	33	Rómulo Gallegos
305	97	San Carlos de Austria
306	97	Juan Ángel Bravo
307	97	Manuel Manrique
308	98	General en Jefe José Laurencio Silva
309	99	Curiapo
310	99	Almirante Luis Brión
311	99	Francisco Aniceto Lugo
312	99	Manuel Renaud
313	99	Padre Barral
314	99	Santos de Abelgas
315	100	Imataca
316	100	Cinco de Julio
317	100	Juan Bautista Arismendi
318	100	Manuel Piar
319	100	Rómulo Gallegos
320	101	Pedernales
321	101	Luis Beltrán Prieto Figueroa
322	102	San José (Delta Amacuro)
323	102	José Vidal Marcano
324	102	Juan Millán
325	102	Leonardo Ruíz Pineda
326	102	Mariscal Antonio José de Sucre
327	102	Monseñor Argimiro García
328	102	San Rafael (Delta Amacuro)
329	102	Virgen del Valle
330	103	Clarines
331	103	Guanape
332	103	Sabana de Uchire
333	104	Capadare
334	104	La Pastora
335	104	Libertador
336	104	San Juan de los Cayos
337	36	Aracua
338	36	La Peña
339	36	San Luis
340	106	Bariro
341	106	Borojó
342	106	Capatárida
343	106	Guajiro
344	106	Seque
345	106	Zazárida
346	106	Valle de Eroa
347	107	Cacique Manaure
348	108	Norte
349	108	Carirubana
350	108	Santa Ana
351	108	Urbana Punta Cardón
352	109	La Vela de Coro
353	109	Acurigua
354	109	Guaibacoa
355	109	Las Calderas
356	109	Macoruca
357	110	Dabajuro
358	111	Agua Clara
359	111	Avaria
360	111	Pedregal
361	111	Piedra Grande
362	111	Purureche
363	112	Adaure
364	112	Adícora
365	112	Baraived
366	112	Buena Vista
367	112	Jadacaquiva
368	112	El Vínculo
369	112	El Hato
370	112	Moruy
371	112	Pueblo Nuevo
372	113	Agua Larga
373	113	El Paují
374	113	Independencia
375	113	Mapararí
376	114	Agua Linda
377	114	Araurima
378	114	Jacura
379	115	Tucacas
380	115	Boca de Aroa
381	116	Los Taques
382	116	Judibana
383	117	Mene de Mauroa
384	117	San Félix
385	117	Casigua
386	83	Guzmán Guillermo
387	83	Mitare
388	83	Río Seco
389	83	Sabaneta
390	83	San Antonio
391	83	San Gabriel
392	83	Santa Ana
393	119	Boca del Tocuyo
394	119	Chichiriviche
395	119	Tocuyo de la Costa
396	120	Palmasola
397	121	Cabure
398	121	Colina
399	121	Curimagua
400	22	San José de la Costa
401	22	Píritu
402	123	San Francisco
403	49	Sucre
404	49	Pecaya
405	125	Tocópero
406	126	El Charal
407	126	Las Vegas del Tuy
408	126	Santa Cruz de Bucaral
409	127	Bruzual
410	127	Urumaco
411	52	Puerto Cumarebo
412	52	La Ciénaga
413	52	La Soledad
414	52	Pueblo Cumarebo
415	52	Zazárida
416	113	Churuguara
417	129	Camaguán
418	129	Puerto Miranda
419	129	Uverito
420	130	Chaguaramas
421	131	El Socorro
422	40	Tucupido
423	40	San Rafael de Laya
424	133	Altagracia de Orituco
425	133	San Rafael de Orituco
426	133	San Francisco Javier de Lezama
427	133	Paso Real de Macaira
428	133	Carlos Soublette
429	133	San Francisco de Macaira
430	133	Libertad de Orituco
431	134	Cantaclaro
432	134	San Juan de los Morros
433	134	Parapara
434	135	El Sombrero
435	135	Sosa
436	136	Las Mercedes
437	136	Cabruta
438	136	Santa Rita de Manapire
439	137	Valle de la Pascua
440	137	Espino
441	138	San José de Unare
442	138	Zaraza
443	139	San José de Tiznados
444	139	San Francisco de Tiznados
445	139	San Lorenzo de Tiznados
446	139	Ortiz
447	140	Guayabal
448	140	Cazorla
449	141	San José de Guaribe
450	141	Uveral
451	142	Santa María de Ipire
452	142	Altamira
453	143	El Calvario
454	143	El Rastro
455	143	Guardatinajas
456	143	Capital Urbana Calabozo
457	54	Quebrada Honda de Guache
458	54	Pío Tamayo
459	54	Yacambú
460	145	Fréitez
461	145	José María Blanco
462	146	Catedral
463	146	Concepción
464	146	El Cují
465	146	Juan de Villegas
466	146	Santa Rosa
467	146	Tamaca
468	146	Unión
469	146	Aguedo Felipe Alvarado
470	146	Buena Vista
471	146	Juárez
472	147	Juan Bautista Rodríguez
473	147	Cuara
474	147	Diego de Lozada
475	147	Paraíso de San José
476	147	San Miguel
477	147	Tintorero
478	147	José Bernardo Dorante
479	147	Coronel Mariano Peraza 
480	148	Bolívar
481	148	Anzoátegui
482	148	Guarico
483	148	Hilario Luna y Luna
484	148	Humocaro Alto
485	148	Humocaro Bajo
486	148	La Candelaria
487	148	Morán
488	149	Cabudare
489	149	José Gregorio Bastidas
490	149	Agua Viva
491	150	Sarare
492	150	Buría
493	150	Gustavo Vegas León
494	151	Trinidad Samuel
495	151	Antonio Díaz
496	151	Camacaro
497	151	Castañeda
498	151	Cecilio Zubillaga
499	151	Chiquinquirá
500	151	El Blanco
501	151	Espinoza de los Monteros
502	151	Lara
503	151	Las Mercedes
504	151	Manuel Morillo
505	151	Montaña Verde
506	151	Montes de Oca
507	151	Torres
508	151	Heriberto Arroyo
509	151	Reyes Vargas
510	151	Altagracia
511	51	Siquisique
512	51	Moroturo
513	51	San Miguel
514	51	Xaguas
515	153	Presidente Betancourt
516	153	Presidente Páez
517	153	Presidente Rómulo Gallegos
518	153	Gabriel Picón González
519	153	Héctor Amable Mora
520	153	José Nucete Sardi
521	153	Pulido Méndez
522	154	La Azulita
523	155	Santa Cruz de Mora
524	155	Mesa Bolívar
525	155	Mesa de Las Palmas
526	156	Aricagua
527	156	San Antonio
528	157	Canagua
529	157	Capurí
530	157	Chacantá
531	157	El Molino
532	157	Guaimaral
533	157	Mucutuy
534	157	Mucuchachí
535	158	Fernández Peña
536	158	Matriz
537	158	Montalbán
538	158	Acequias
539	158	Jají
540	158	La Mesa
541	158	San José del Sur
542	159	Tucaní
543	159	Florencio Ramírez
544	160	Santo Domingo
545	160	Las Piedras
546	161	Guaraque
547	161	Mesa de Quintero
548	161	Río Negro
549	162	Arapuey
550	162	Palmira
551	163	San Cristóbal de Torondoy
552	163	Torondoy
553	42	Antonio Spinetti Dini
554	42	Arias
555	42	Caracciolo Parra Pérez
556	42	Domingo Peña
557	42	El Llano
558	42	Gonzalo Picón Febres
559	42	Jacinto Plaza
560	42	Juan Rodríguez Suárez
561	42	Lasso de la Vega
562	42	Mariano Picón Salas
563	42	Milla
564	42	Osuna Rodríguez
565	42	Sagrario
566	42	El Morro
567	42	Los Nevados
568	83	Andrés Eloy Blanco
569	83	La Venta
570	83	Piñango
571	83	Timotes
572	166	Eloy Paredes
573	166	San Rafael de Alcázar
574	166	Santa Elena de Arenales
575	167	Santa María de Caparo
576	168	Pueblo Llano
577	169	Cacute
578	169	La Toma
579	169	Mucuchíes
580	169	Mucurubá
581	169	San Rafael
582	170	Gerónimo Maldonado
583	170	Bailadores
584	171	Tabay
585	49	Chiguará
586	49	Estánquez
587	49	Lagunillas
588	49	La Trampa
589	49	Pueblo Nuevo del Sur
590	49	San Juan
591	50	El Amparo
592	50	El Llano
593	50	San Francisco
594	50	Tovar
595	174	Independencia
596	174	María de la Concepción Palacios Blanco
597	174	Nueva Bolivia
598	174	Santa Apolonia
599	175	Caño El Tigre
600	175	Zea
601	176	Aragüita
602	176	Arévalo González
603	176	Capaya
604	176	Caucagua
605	176	Panaquire
606	176	Ribas
607	176	El Café
608	176	Marizapa
609	154	Cumbo
610	154	San José de Barlovento
611	178	El Cafetal
612	178	Las Minas
613	178	Nuestra Señora del Rosario
614	179	Higuerote
615	179	Curiepe
616	179	Tacarigua de Brión
617	180	Mamporal
618	181	Carrizal
619	182	Chacao
620	183	Charallave
621	183	Las Brisas
622	184	El Hatillo
623	185	Altagracia de la Montaña
624	185	Cecilio Acosta
625	185	Los Teques
626	185	El Jarillo
627	185	San Pedro
628	185	Tácata
629	185	Paracotos
630	15	Cartanal
631	15	Santa Teresa del Tuy
632	187	La Democracia
633	187	Ocumare del Tuy
634	187	Santa Bárbara
635	188	San Antonio de los Altos
636	31	Río Chico
637	31	El Guapo
638	31	Tacarigua de la Laguna
639	31	Paparo
640	31	San Fernando del Guapo
641	190	Santa Lucía del Tuy
642	191	Cúpira
643	191	Machurucuto
644	192	Guarenas
645	26	San Antonio de Yare
646	26	San Francisco de Yare
647	49	Leoncio Martínez
648	49	Petare
649	49	Caucagüita
650	49	Filas de Mariche
651	49	La Dolorita
652	51	Cúa
653	51	Nueva Cúa
654	52	Guatire
655	52	Bolívar
656	104	San Antonio de Maturín
657	104	San Francisco de Maturín
658	198	Aguasay
659	36	Caripito
660	200	El Guácharo
661	200	La Guanota
662	200	Sabana de Piedra
663	200	San Agustín
664	200	Teresen
665	200	Caripe
666	66	Areo
667	66	Capital Cedeño
668	66	San Félix de Cantalicio
669	66	Viento Fresco
670	60	El Tejero
671	60	Punta de Mata
672	42	Chaguaramas
673	42	Las Alhuacas
674	42	Tabasca
675	42	Temblador
676	204	Alto de los Godos
677	204	Boquerón
678	204	Las Cocuizas
679	204	La Cruz
680	204	San Simón
681	204	El Corozo
682	204	El Furrial
683	204	Jusepín
684	204	La Pica
685	204	San Vicente
686	70	Aparicio
687	70	Aragua de Maturín
688	70	Chaguamal
689	70	El Pinto
690	70	Guanaguana
691	70	La Toscana
692	70	Taguaya
693	206	Cachipo
694	206	Quiriquire
695	207	Santa Bárbara
696	208	Barrancas
697	208	Los Barrancos de Fajardo
698	209	Uracoa
699	210	Antolín del Campo
700	56	Arismendi
701	212	García
702	212	Francisco Fajardo
703	213	Bolívar
704	213	Guevara
705	213	Matasiete
706	213	Santa Ana
707	213	Sucre
708	214	Aguirre
709	214	Maneiro
710	215	Adrián
711	215	Juan Griego
712	215	Yaguaraparo
713	216	Porlamar
714	217	San Francisco de Macanao
715	217	Boca de Río
716	218	Tubores
717	218	Los Baleales
718	219	Vicente Fuentes
719	219	Villalba
720	220	San Juan Bautista
721	220	Zabala
722	221	Capital Araure
723	221	Río Acarigua
724	222	Capital Esteller
725	222	Uveral
726	223	Guanare
727	223	Córdoba
728	223	San José de la Montaña
729	223	San Juan de Guanaguanare
730	223	Virgen de la Coromoto
731	224	Guanarito
732	224	Trinidad de la Capilla
733	224	Divina Pastora
734	225	Monseñor José Vicente de Unda
735	225	Peña Blanca
736	226	Capital Ospino
737	226	Aparición
738	226	La Estación
739	31	Páez
740	31	Payara
741	31	Pimpinela
742	31	Ramón Peraza
743	228	Papelón
744	228	Caño Delgadito
745	229	San Genaro de Boconoito
746	229	Antolín Tovar
747	230	San Rafael de Onoto
748	230	Santa Fe
749	230	Thermo Morles
750	231	Santa Rosalía
751	231	Florida
752	49	Sucre
753	49	Concepción
754	49	San Rafael de Palo Alzado
755	49	Uvencio Antonio Velásquez
756	49	San José de Saguaz
757	49	Villa Rosa
758	233	Turén
759	233	Canelones
760	233	Santa Cruz
761	233	San Isidro Labrador
762	54	Mariño
763	54	Rómulo Gallegos
764	235	San José de Aerocuar
765	235	Tavera Acosta
766	56	Río Caribe
767	56	Antonio José de Sucre
768	56	El Morro de Puerto Santo
769	56	Puerto Santo
770	56	San Juan de las Galdonas
771	237	El Pilar
772	237	El Rincón
773	237	General Francisco Antonio Váquez
774	237	Guaraúnos
775	237	Tunapuicito
776	237	Unión
777	238	Santa Catalina
778	238	Santa Rosa
779	238	Santa Teresa
780	238	Bolívar
781	238	Maracapana
782	239	Libertad
783	239	El Paujil
784	239	Yaguaraparo
785	240	Cruz Salmerón Acosta
786	240	Chacopata
787	240	Manicuare
788	42	Tunapuy
789	42	Campo Elías
790	216	Irapa
791	216	Campo Claro
792	216	Maraval
793	216	San Antonio de Irapa
794	216	Soro
795	243	Mejía
796	244	Cumanacoa
797	244	Arenas
798	244	Aricagua
799	244	Cogollar
800	244	San Fernando
801	244	San Lorenzo
802	245	Villa Frontado (Muelle de Cariaco)
803	245	Catuaro
804	245	Rendón
805	245	San Cruz
806	245	Santa María
807	49	Altagracia
808	49	Santa Inés
809	49	Valentín Valiente
810	49	Ayacucho
811	49	San Juan
812	49	Raúl Leoni
813	49	Gran Mariscal
814	247	Cristóbal Colón
815	247	Bideau
816	247	Punta de Piedras
817	247	Güiria
818	154	Andrés Bello
819	249	Antonio Rómulo Costa
820	250	Ayacucho
821	250	Rivas Berti
822	250	San Pedro del Río
823	36	Bolívar
824	36	Palotal
825	36	General Juan Vicente Gómez
826	36	Isaías Medina Angarita
827	252	Cárdenas
828	252	Amenodoro Ángel Lamus
829	252	La Florida
830	253	Córdoba
831	254	Fernández Feo
832	254	Alberto Adriani
833	254	Santo Domingo
834	20	Francisco de Miranda
835	256	García de Hevia
836	256	Boca de Grita
837	256	José Antonio Páez
838	257	Guásimos
839	15	Independencia
840	15	Juan Germán Roscio
841	15	Román Cárdenas
842	259	Jáuregui
843	259	Emilio Constantino Guerrero
844	259	Monseñor Miguel Antonio Salas
845	260	José María Vargas
846	261	Junín
847	261	La Petrólea
848	261	Quinimarí
849	261	Bramón
850	19	Libertad
851	19	Cipriano Castro
852	19	Manuel Felipe Rugeles
853	42	Libertador
854	42	Doradas
855	42	Emeterio Ochoa
856	42	San Joaquín de Navay
857	264	Lobatera
858	264	Constitución
859	265	Michelena
860	266	Panamericano
861	266	La Palmita
862	267	Pedro María Ureña
863	267	Nueva Arcadia
864	268	Delicias
865	268	Pecaya
866	269	Samuel Darío Maldonado
867	269	Boconó
868	269	Hernández
869	270	La Concordia
870	270	San Juan Bautista
871	270	Pedro María Morantes
872	270	San Sebastián
873	270	Dr. Francisco Romero Lobo
874	271	Seboruco
875	27	Simón Rodríguez
876	49	Sucre
877	49	Eleazar López Contreras
878	49	San Pablo
879	274	Torbes
880	275	Uribante
881	275	Cárdenas
882	275	Juan Pablo Peñalosa
883	275	Potosí
884	276	San Judas Tadeo
885	154	Araguaney
886	154	El Jaguito
887	154	La Esperanza
888	154	Santa Isabel
889	278	Boconó
890	278	El Carmen
891	278	Mosquey
892	278	Ayacucho
893	278	Burbusay
894	278	General Ribas
895	278	Guaramacal
896	278	Vega de Guaramacal
897	278	Monseñor Jáuregui
898	278	Rafael Rangel
899	278	San Miguel
900	278	San José
901	36	Sabana Grande
902	36	Cheregüé
903	36	Granados
904	280	Arnoldo Gabaldón
905	280	Bolivia
906	280	Carrillo
907	280	Cegarra
908	280	Chejendé
909	280	Manuel Salvador Ulloa
910	280	San José
911	281	Carache
912	281	La Concepción
913	281	Cuicas
914	281	Panamericana
915	281	Santa Cruz
916	282	Escuque
917	282	La Unión
918	282	Santa Rita
919	282	Sabana Libre
920	283	El Socorro
921	283	Los Caprichos
922	283	Antonio José de Sucre
923	284	Campo Elías
924	284	Arnoldo Gabaldón
925	285	Santa Apolonia
926	285	El Progreso
927	285	La Ceiba
928	285	Tres de Febrero
929	83	El Dividive
930	83	Agua Santa
931	83	Agua Caliente
932	83	El Cenizo
933	83	Valerita
934	287	Monte Carmelo
935	287	Buena Vista
936	287	Santa María del Horcón
937	288	Motatán
938	288	El Baño
939	288	Jalisco
940	289	Pampán
941	289	Flor de Patria
942	289	La Paz
943	289	Santa Ana
944	290	Pampanito
945	290	La Concepción
946	290	Pampanito II
947	291	Betijoque
948	291	José Gregorio Hernández
949	291	La Pueblita
950	291	Los Cedros
951	292	Carvajal
952	292	Campo Alegre
953	292	Antonio Nicolás Briceño
954	292	José Leonardo Suárez
955	49	Sabana de Mendoza
956	49	Junín
957	49	Valmore Rodríguez
958	49	El Paraíso
959	294	Andrés Linares
960	294	Chiquinquirá
961	294	Cristóbal Mendoza
962	294	Cruz Carrillo
963	294	Matriz
964	294	Monseñor Carrillo
965	294	Tres Esquinas
966	51	Cabimbú
967	51	Jajó
968	51	La Mesa de Esnujaque
969	51	Santiago
970	51	Tuñame
971	51	La Quebrada
972	296	Juan Ignacio Montilla
973	296	La Beatriz
974	296	La Puerta
975	296	Mendoza del Valle de Momboy
976	296	Mercedes Díaz
977	296	San Luis
978	297	Caraballeda
979	297	Carayaca
980	297	Carlos Soublette
981	297	Caruao Chuspa
982	297	Catia La Mar
983	297	El Junko
984	297	La Guaira
985	297	Macuto
986	297	Maiquetía
987	297	Naiguatá
988	297	Urimare
989	298	Arístides Bastidas
990	36	Bolívar
991	300	Chivacoa
992	300	Campo Elías
993	301	Cocorote
994	15	Independencia
995	303	José Antonio Páez
996	304	La Trinidad
997	305	Manuel Monge
998	306	Salóm
999	306	Temerla
1000	306	Nirgua
1001	307	San Andrés
1002	307	Yaritagua
1003	308	San Javier
1004	308	Albarico
1005	308	San Felipe
1006	49	Sucre
1007	310	Urachiche
1008	311	El Guayabo
1009	311	Farriar
1010	312	Isla de Toas
1011	312	Monagas
1012	313	San Timoteo
1013	313	General Urdaneta
1014	313	Libertador
1015	313	Marcelino Briceño
1016	313	Pueblo Nuevo
1017	313	Manuel Guanipa Matos
1018	314	Ambrosio
1019	314	Carmen Herrera
1020	314	La Rosa
1021	314	Germán Ríos Linares
1022	314	San Benito
1023	314	Rómulo Betancourt
1024	314	Jorge Hernández
1025	314	Punta Gorda
1026	314	Arístides Calvani
1027	315	Encontrados
1028	315	Udón Pérez
1029	316	Moralito
1030	316	San Carlos del Zulia
1031	316	Santa Cruz del Zulia
1032	316	Santa Bárbara
1033	316	Urribarrí
1034	317	Carlos Quevedo
1035	317	Francisco Javier Pulgar
1036	317	Simón Rodríguez
1037	317	Guamo-Gavilanes
1038	318	La Concepción
1039	318	San José
1040	318	Mariano Parra León
1041	318	José Ramón Yépez
1042	319	Jesús María Semprún
1043	319	Barí
1044	320	Concepción
1045	320	Andrés Bello
1046	320	Chiquinquirá
1047	320	El Carmelo
1048	320	Potreritos
1049	321	Libertad
1050	321	Alonso de Ojeda
1051	321	Venezuela
1052	321	Eleazar López Contreras
1053	321	Campo Lara
1054	322	Bartolomé de las Casas
1055	322	Libertad
1056	322	Río Negro
1057	322	San José de Perijá
1058	323	San Rafael
1059	323	La Sierrita
1060	323	Las Parcelas
1061	323	Luis de Vicente
1062	323	Monseñor Marcos Sergio Godoy
1063	323	Ricaurte
1064	323	Tamare
1065	324	Antonio Borjas Romero
1066	324	Bolívar
1067	324	Cacique Mara
1068	324	Carracciolo Parra Pérez
1069	324	Cecilio Acosta
1070	324	Cristo de Aranza
1071	324	Coquivacoa
1072	324	Chiquinquirá
1073	324	Francisco Eugenio Bustamante
1074	324	Idelfonzo Vásquez
1075	324	Juana de Ávila
1076	324	Luis Hurtado Higuera
1077	324	Manuel Dagnino
1078	324	Olegario Villalobos
1079	324	Raúl Leoni
1080	324	Santa Lucía
1081	324	Venancio Pulgar
1082	324	San Isidro
1083	83	Altagracia
1084	83	Faría
1085	83	Ana María Campos
1086	83	San Antonio
1087	83	San José
1088	326	Donaldo García
1089	326	El Rosario
1090	326	Sixto Zambrano
1091	123	San Francisco
1092	123	El Bajo
1093	123	Domitila Flores
1094	123	Francisco Ochoa
1095	123	Los Cortijos
1096	123	Marcial Hernández
1097	328	Santa Rita
1098	328	El Mene
1099	328	Pedro Lucas Urribarrí
1100	328	José Cenobio Urribarrí
1101	26	Rafael Maria Baralt
1102	26	Manuel Manrique
1103	26	Rafael Urdaneta
1104	49	Bobures
1105	49	Gibraltar
1106	49	Heras
1107	49	Monseñor Arturo Álvarez
1108	49	Rómulo Gallegos
1109	49	El Batey
1110	331	Rafael Urdaneta
1111	331	La Victoria
1112	331	Raúl Cuenca
1113	31	Sinamaica
1114	31	Alta Guajira
1115	31	Elías Sánchez Rubio
1116	31	Guajira
1117	42	Altagracia
1118	42	Antímano
1119	42	Caricuao
1120	42	Catedral
1121	42	Coche
1122	42	El Junquito
1123	42	El Paraíso
1124	42	El Recreo
1125	42	El Valle
1126	42	La Candelaria
1127	42	La Pastora
1128	42	La Vega
1129	42	Macarao
1130	42	San Agustín
1131	42	San Bernardino
1132	42	San José
1133	42	San Juan
1134	42	San Pedro
1135	42	Santa Rosalía
1136	42	Santa Teresa
1137	42	Sucre (Catia)
1138	42	23 de enero
"""

lines = parishes_data.strip().split("\n")[1:]

parishes = []
for line in lines:
    parts = line.split('\t')
    if len(parts) >= 3:
        parishes.append({
            "id": int(parts[0]),
            "municipality_id": int(parts[1]),
            "name": parts[2].strip().replace("'", "\\'")
        })

output = "        $parishes = [\n"
for p in parishes:
    output += f"            ['id' => {p['id']}, 'municipality_id' => {p['municipality_id']}, 'name' => '{p['name']}'],\n"
output += "        ];\n"
output += "        foreach (array_chunk($parishes, 100) as $chunk) {\n"
output += "            DB::table('parishes')->insert($chunk);\n"
output += "        }\n"

with open("append.txt", "w", encoding="utf-8") as f:
    f.write(output)
