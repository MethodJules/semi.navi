#!/usr/bin/python
# coding=latin-1
from flask import Flask
from flask import request
from neo4j import GraphDatabase
from neo4jClass import neo4jConnector
import time
import json
import re
import unidecode
import os
import spacy
from annoy import AnnoyIndex
import getEntities
import sys

app = Flask(__name__)

# Database Credentials

uri             = "bolt://neo4j:7687"

userName        = "neo4j"

password        = "test"





# Connect to the neo4j database server
#Sleep few seconds
time.sleep(60)

#driver  = GraphDatabase.driver(uri, auth=(userName, password), encrypted=False)

driver = neo4jConnector()

# Beim Starten der Anwendung Spacy in den Arbeitsspeicher laden. Damit dieser Vorgang schneller geht, werden die Teilmodule tagger und ner nicht geladen, da diese nicht benötigt werden.
nlp = spacy.load('de_core_news_sm', disable=["tagger", "ner"])

# Standardmäßig sind die Stop Words in Spacy nicht aktiviert. Die Aktivierung muss einmal durchgeführt werden.
for word in nlp.Defaults.stop_words:
    lex = nlp.vocab[word]
    lex.is_stop = True


@app.route('/')
def hello_world():
    return 'Hallo'

# Gibt alle Entitäten und Relationen wieder. In der Klasse neo4jClass steht bei den Methoden eine genaurere Beschreibung wofür diese verwendet werden.
@app.route("/get-entities")
def get_entities():
    msg_arr = {}
    try:
        result = driver.get_entities()
        msg_arr['type'] = 'success'
        msg_arr['result'] = result
    except Exception as e:
        msg_arr['type'] = 'error'
        msg_arr['result'] = str(e)

    return json.dumps(msg_arr, ensure_ascii=False).encode(encoding='utf-8')
# Gibt gefundene Nodes für die Filtersuche zurück.
@app.route("/get-nodes-by-filter", methods=['POST'])
def get_nodes_by_filter():
    if request.method == 'POST':
        filter = request.form['filter']
        #print(type(filter))
        #print(filter)
        if (len(filter) > 0):

            if (len(filter) > 0 and filter != None):

                msg_arr = {}
                #result_dict = []
                print(filter)
                try:

                    json_dict = json.loads(filter, encoding="utf-8")
                    result_dict = driver.get_nodes_by_filter(json_dict)
                    print('...')
                    print(type(result_dict))
                    print(*result_dict)
                    msg_arr['type'] = 'success'
                    msg_arr['result'] = result_dict
                    print(msg_arr)
                except Exception as e:
                    msg_arr['type'] = 'error'
                    msg_arr['result'] = str(e)
                return json.dumps(msg_arr, ensure_ascii=False).encode(encoding='utf-8')
        else:
            return "No post parameters"


# Diese Funktion ist für die Volltextsuche bei der semantischen Suche zuständig.
@app.route("/semantic-search", methods=['POST'])
def semantic_search():

    if request.method == 'POST':
        text = request.form['search_query']



        if (len(text) > 0):
            msg_arr = {}

            # Bestimmte Zeichen entfernen. Text in Klammern wird komplett entfernt.
            text = text.strip()
            text = re.sub(r'\([^()]*\)', '', text)
            text = text.replace('"', '')
            text = text.replace('“', '')
            text = text.replace("‘", '')
            text = text.replace("’", '')
            text = text.replace("”", '')
            text = text.replace("'", '')
            text = text.replace('/', ' ')
            text = text.replace("  ", " ")
            text = unidecode.unidecode(text)

            for word in nlp.Defaults.stop_words:
                lex = nlp.vocab[word]
                lex.is_stop = True

            result_list = []
            doc = nlp(text.lower())

            # Alle Stop Words entfernen und die verbleibenden Wörter lemmatisieren, sodass die Ursprungsform in dem Array steht. In der Datenbank sind die Tags
            # ebenfalls lemmatisiert und können so miteinander verglichen werden
            shorten_doc = nlp(' '.join([str(t) for t in doc if not t.is_stop]))
            shorten_doc_lemma_list = []
            shorten_doc_lemma = ""
            for token in shorten_doc:
                # Punkte, Kommata etc. nicht mit berücksichtigen
                if (token.lemma_ not in ['.', ',', ';', '-']):
                    shorten_doc_lemma_list.append(token.lemma_)
                    shorten_doc_lemma += token.lemma_ + " "

            shorten_doc_lemma = shorten_doc_lemma.replace('  ', ' ').strip()


            if (len(shorten_doc_lemma_list) > 0):
                try:

                    # Neues Spacy Doc aus dem gekürzten und lemmatisierten Suchstring erzeugen
                    doc = nlp(shorten_doc_lemma)

                    # Den Suchindex laden. In diesem sind alle Vektoren der Sentences und Clauses zusammen mit der ID des Hauptknoten abgespeichert.
                    ann = AnnoyIndex(300)
                    ann.load('search_index.ann')

                    # Den Vektor vom Suchstring übergeben und die 50 nächsten Nachbarn anhand der Vektoren zurückgeben.
                    similar_ids = ann.get_nns_by_vector(doc.vector, 50)

                    if (len(similar_ids) > 0):

                        # Konfigurationsdatei laden und Similarity Score auslesen
                        try:
                            file_path = os.path.dirname(os.path.abspath(__file__))
                            config_path = os.path.join(file_path, 'config.json')
                            file = open(config_path, 'r', encoding="utf8")
                            data = file.read()
                            config = json.loads(data)

                        except:
                            raise

                        similarity_score = float(config['similarity_score'])

                        # Die Informationen 50 nächsten Sätze aus dem Suchindex aus der Datenbank laden
                        result = driver.get_sent_clauses_by_id(similar_ids)

                        # Alle Sätze iterieren und mit der Funktion doc.similarity von Spacy mit dem Suchstring vergleichen. Die Ähnlichkeit der nächsten 50 Sätze
                        # ist nicht klar. Dies kann eine hohe Ähnlichkeit von z.B. mehr als 80% sein, die Ähnlichkeit kann aber auch nur 10% betragen. Deshalb werden für diese 50
                        # Sätze noch einmal die Ähnlichkeiten verglichen und nur diejenigen über einen gewissen Ähnlichkeitsschwellwert werden in die Ergebnismenge mit einbezogen.
                        # Es könnten auch einfach alle Sätze und Clauses aus der Datenbank auf Ähnlichkeit untersucht wurden, nur dauert dieses erheblich länger.
                        for sent in result['sentences']:
                            if (sent['node_id'] not in [elem['node_id'] for elem in result_list]):
                                nlp_sent = nlp(sent['shorten_original'].lower())

                                similarity = doc.similarity(nlp_sent)
                                if (similarity > similarity_score):
                                    res_dict = {}
                                    res_dict['node_id'] = sent['node_id']
                                    res_dict['node_title'] = sent['node_title']
                                    res_dict['node_created'] = sent['node_created']
                                    res_dict['node_changed'] = sent['node_changed']
                                    res_dict['sents'] = sent['sent']
                                    res_dict['similarity'] = similarity

                                    result_list.append(res_dict)

                        for clause in result['clauses']:
                            if (clause['node_id'] not in [elem['node_id'] for elem in result_list]):
                                nlp_clause = nlp(clause['shorten_clause'].lower())

                                similarity = doc.similarity(nlp_clause)
                                if (similarity > similarity_score):
                                    res_dict = {}
                                    res_dict['node_id'] = clause['node_id']
                                    res_dict['node_title'] = clause['node_title']
                                    res_dict['node_created'] = clause['node_created']
                                    res_dict['node_changed'] = clause['node_changed']
                                    res_dict['sents'] = clause['sent']
                                    res_dict['similarity'] = similarity

                                    result_list.append(res_dict)

                    # Ergebnisliste anhand des Ähnlichkeitswertes sortieren
                    result_list = sorted(result_list, key=lambda x: x['similarity'], reverse=True)

                    # Zusätzlich werden alle Hauptknoten und ihre Sätze geladen, in denen die Suchwörter als Tag oder als Synonym innerhalb eines Satzes auftauchen.
                    result = driver.get_tag_syn_for_sent(shorten_doc_lemma_list)

                    # Liefert die vorhergehende Funktion keine Ergebnisse wird die Suche auf Inhalt der gesamten Node ausgeweitet und die Tags und Synonyme müssen nicht
                    # mehr nur in einem einzigen Satz vorkommen
                    if (len(result) == 0):
                        result = driver.get_tag_syn_for_node(shorten_doc_lemma_list)

                    if (len(result) > 0):
                        for res in result:
                            result_list.append(res)

                    msg_arr['type'] = 'success'
                    msg_arr['result'] = result_list
                    print(msg_arr)
                except Exception as e:
                    msg_arr['type'] = 'error'
                    msg_arr['result'] = str(e)


            return json.dumps(msg_arr, ensure_ascii=False).encode(encoding='utf-8')
        else:
            return "No post parameters"


# Liefer die Anzahl der Hauptknoten aus der Datenbank zurück.
@app.route("/get-nodes-count", methods=['POST'])
def get_nodes_count():

    #return 'Okay'
    if request.method == 'POST':
        content_type = request.form['content_type']

        if (len(content_type) > 0):
            msg_arr= {}
            try:
                result = driver.get_nodes_count(content_type)
                msg_arr['type'] = 'success'
                msg_arr['result'] = result
            except Exception as e:
                msg_arr['type'] = 'error'
                msg_arr['result'] = str(e)

            return json.dumps(msg_arr, ensure_ascii=False).encode(encoding='utf-8')
        else:
            return "No post parameters"

# Holt alle Entitäten passend zu einer Node ID einer Drupal Node aus der Datenbank.
@app.route("/get-entities-by-id", methods=['POST'])
def get_entities_by_id():

    if request.method == 'POST':
        node_id = request.form['node_id']

        if (len(node_id) > 0):

            msg_arr = {}
            try:
                result = driver.get_entities_by_id(node_id)
                msg_arr['type'] = 'success'
                msg_arr['result'] = result
            except Exception as e:
                msg_arr['type'] = 'error'
                msg_arr['result'] = str(e)

            return json.dumps(msg_arr, ensure_ascii=False).encode(encoding='utf-8')
        else:
            return "No post parameters"

# Holt alle Entitäten und dazu Relationen passend zu einer Node ID einer Drupal Node aus der Datenbank.
@app.route("/get-entities-relations-by-id", methods=['POST'])
def get_entities_relations_by_id():

    if request.method == 'POST':
        node_id = request.form['node_id']

        if (len(node_id) > 0):
            msg_arr= {}

            try:
                res_array = {}
                res_array['entities'] = driver.get_entities_by_id(node_id)
                res_array['relations'] = driver.get_relations_by_id(node_id)

                msg_arr['type'] = 'success'
                msg_arr['result'] = res_array
            except Exception as e:
                msg_arr['type'] = 'error'
                msg_arr['result'] = str(e)

            return json.dumps(msg_arr, ensure_ascii=False).encode(encoding='utf-8')
        else:
            return "No post parameters"

# Liefert alle Entitäten und Relationen von einer anderen Entität aus
@app.route("/get-entities-relations-by-entity", methods=['POST'])
def get_entities_relations_by_entity():

    if request.method == 'POST':
        ent_text = request.form['ent_text']
        ent_ner = request.form['ent_ner']

        if (len(ent_text) > 0 and len(ent_ner) > 0):

            msg_arr = {}

            try:
                res_array = {}
                res_array['root_nodes'] = driver.get_node_by_entity(ent_text, ent_ner);
                res_array['entities_relations'] = driver.get_entities_relations_by_entity(ent_text, ent_ner)

                ent_list = []
                for ent in res_array['entities_relations']:
                    if (ent['ent2_text'] not in ent_list):
                        ent_list.append(ent['ent2_text'])

                res_array['additional_relations'] = driver.get_additional_relations(ent_list)

                msg_arr['type'] = 'success'
                msg_arr['result'] = res_array
            except Exception as e:
                msg_arr['type'] = 'error'
                msg_arr['result'] = str(e)

            return json.dumps(msg_arr, ensure_ascii=False).encode(encoding='utf-8')
        else:
            return "No post parameters"

# Übergibt erhaltene abgeänderte Entitäten an neo4jClass und dort werden die Änderungen in der Datenbank gespeichert.
@app.route("/change-entities", methods=['POST'])
def change_entities():

    if request.method == 'POST':
        entities = request.form['entities']

        if (len(entities) > 0):
            msg_arr = {}
            try:
                json_dict = json.loads(entities, encoding="utf-8")
                result = driver.change_entities(json_dict)

                msg_arr['type'] = 'success'
            except Exception as e:
                msg_arr['type'] = 'error'
                msg_arr['result'] = str(e)

            return json.dumps(msg_arr, ensure_ascii=False).encode(encoding='utf-8')
        else:
            return "No post parameters"

# Gibt zurück, ob eine Enität mit einem bestimmten Text bereits in der Datenbank existiert oder nicht.
@app.route("/check-entity-exists", methods=['POST'])
def check_entity_exists():
    if request.method == 'POST':
        entity = request.form['entity']

        if (len(entity) > 0):

            msg_arr = {}
            try:
                result = driver.check_entity_exists(entity)

                msg_arr['type'] = 'success'
                if (len(result) > 0):
                    msg_arr['result'] = 'true'
                else:
                    msg_arr['result'] = 'false'
            except Exception as e:
                msg_arr['type'] = 'error'
                msg_arr['result'] = str(e)


            return json.dumps(msg_arr, ensure_ascii=False).encode(encoding='utf-8')
        else:
            return "No post parameters"

# Gibt eine Entität mit Text und NER an neo4jClass, wo diese dann in der Datenbank gespeichert und die Verknüpfung zu Sätzen von den Hauptknoten erzeugt wird.
@app.route("/add-entity", methods=['POST'])
def add_entity():
    if request.method == 'POST':
        entity_ner = request.form['entity_ner']
        entity_text = request.form['entity_text']

        if (len(entity_text) > 0 and len(entity_ner) > 0):

            msg_arr = {}

            try:
                result = driver.add_entity(entity_text, entity_ner)

                msg_arr['type'] = 'success'
                msg_arr['result'] = result
            except Exception as e:
                msg_arr['type'] = 'error'
                msg_arr['result'] = str(e)

            return json.dumps(msg_arr, ensure_ascii=False).encode(encoding='utf-8')
        else:
            return "No post parameters"

# Löscht eine Node, deren Content Fields, Sentences, Clauses und Tags aus der Datenbank.
@app.route("/del-node", methods=['POST'])
def del_node():
    if request.method == 'POST':
        node_id = request.form['node_id']

        if (len(node_id) > 0):
            msg_arr = {}

            try:
                result = driver.del_node(str(node_id))
                msg_arr['type'] = 'success'
                msg_arr['result'] = result
            except Exception as e:
                msg_arr['type'] = 'error'
                msg_arr['result'] = str(e)

            return json.dumps(msg_arr, ensure_ascii=False).encode(encoding='utf-8')
        else:
            return "No post parameters"

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0')

