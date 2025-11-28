-- USER CREATION
CREATE USER etudiant_esen IDENTIFIED BY esen
DEFAULT TABLESPACE users
QUOTA 10M ON users;

-- PRIVILEGES
GRANT CONNECT, RESOURCE TO etudiant_esen;

-- TABLE ADMIN
CREATE TABLE Admin (
    admin_id NUMBER PRIMARY KEY,
    username VARCHAR2(50),
    admin_password VARCHAR2(50),
    admin_name VARCHAR2(100),
    email VARCHAR2(100)
);

-- TABLE MEDECIN
CREATE TABLE Medecin (
    medecin_id NUMBER PRIMARY KEY,
    nom VARCHAR2(100),
    specialite VARCHAR2(100),
    telephone VARCHAR2(20),
    email VARCHAR2(100)
);

-- TABLE PATIENT
CREATE TABLE Patient (
    patient_id NUMBER PRIMARY KEY,
    nom VARCHAR2(100),
    adresse VARCHAR2(255),
    telephone VARCHAR2(20),
    email VARCHAR2(100)
);

-- TABLE RENDEZVOUS
CREATE TABLE RendezVous (
    rdv_id NUMBER PRIMARY KEY,
    patient_id NUMBER,
    medecin_id NUMBER,
    date_consultation DATE,
    statut VARCHAR2(50),
    prix NUMBER,
    CONSTRAINT fk_patient FOREIGN KEY (patient_id) REFERENCES Patient(patient_id),
    CONSTRAINT fk_medecin FOREIGN KEY (medecin_id) REFERENCES Medecin(medecin_id)
);

-- TABLE CONSULTATION
CREATE TABLE Consultation (
    consultation_id NUMBER PRIMARY KEY,
    patient_id NUMBER,
    admin_id NUMBER,
    date_consultation DATE,
    prix NUMBER,
    diagnostique VARCHAR2(255),
    CONSTRAINT fk_cons_patient FOREIGN KEY (patient_id) REFERENCES Patient(patient_id),
    CONSTRAINT fk_cons_admin FOREIGN KEY (admin_id) REFERENCES Admin(admin_id)
);

-- INSERTION MANUELLE MÉDECINS
INSERT INTO Medecin VALUES (1, 'Dr. Ali', 'Cardiologue', '22334455', 'ali@med.tn');
INSERT INTO Medecin VALUES (2, 'Dr. Salma', 'Dentiste', '22334456', 'salma@med.tn');

-- INSERTION MANUELLE PATIENTS
INSERT INTO Patient VALUES (1, 'Mohamed', 'Tunis', '20101010', 'mohamed@gmail.com');
INSERT INTO Patient VALUES (2, 'Nour', 'Sfax', '20202020', 'nour@gmail.com');

-- CURSEUR IMPLICITE POUR INSÉRER UN MÉDECIN
SET SERVEROUTPUT ON
ACCEPT v_id PROMPT 'Entrez ID Médecin: ';
ACCEPT v_nom PROMPT 'Entrez nom Médecin: ';
ACCEPT v_spec PROMPT 'Entrez spécialité: ';
ACCEPT v_tel PROMPT 'Entrez téléphone: ';
ACCEPT v_email PROMPT 'Entrez email: ';

DECLARE
    v_id NUMBER;
    v_nom VARCHAR2(100);
    v_spec VARCHAR2(100);
    v_tel VARCHAR2(20);
    v_email VARCHAR2(100);
BEGIN
    v_id := &v_id;
    v_nom := '&v_nom';
    v_spec := '&v_spec';
    v_tel := '&v_tel';
    v_email := '&v_email';

    INSERT INTO Medecin VALUES(v_id, v_nom, v_spec, v_tel, v_email);
    DBMS_OUTPUT.PUT_LINE('Médecin ajouté avec succès');
END;
/

-- CURSEUR EXPLICITE POUR AFFICHER SPÉCIALITÉS & EMAILS
SET SERVEROUTPUT ON
DECLARE
    CURSOR med_cursor IS SELECT specialite, email FROM Medecin;
    med_rec med_cursor%ROWTYPE;
BEGIN
    OPEN med_cursor;
    FETCH med_cursor INTO med_rec;
    WHILE med_cursor%FOUND LOOP
        DBMS_OUTPUT.PUT_LINE('Spécialité: ' || med_rec.specialite || ' | Email: ' || med_rec.email);
        FETCH med_cursor INTO med_rec;
    END LOOP;
    CLOSE med_cursor;
END;
/

-- TRIGGER POUR ENREGISTRER OPÉRATIONS SUR MÉDECIN
CREATE TABLE medecin_operations (
    operation_type VARCHAR2(10),
    operation_date DATE,
    medecin_id NUMBER,
    nom VARCHAR2(100),
    specialite VARCHAR2(100)
);

CREATE OR REPLACE TRIGGER trg_medecin_ops
AFTER INSERT OR UPDATE OR DELETE ON Medecin
FOR EACH ROW
DECLARE
    v_type VARCHAR2(10);
BEGIN
    IF INSERTING THEN v_type := 'INSERT';
    ELSIF UPDATING THEN v_type := 'UPDATE';
    ELSIF DELETING THEN v_type := 'DELETE';
    END IF;

    INSERT INTO medecin_operations(operation_type, operation_date, medecin_id, nom, specialite)
    VALUES(v_type, SYSDATE, :OLD.medecin_id, :OLD.nom, :OLD.specialite);
END;
/

-- TEST TRIGGER PAR UPDATE
SET SERVEROUTPUT ON
ACCEPT m_id PROMPT 'Entrez ID médecin à mettre à jour : ';
ACCEPT m_spec PROMPT 'Nouvelle spécialité : ';

DECLARE
    v_id NUMBER := &m_id;
    v_spec VARCHAR2(100) := '&m_spec';
BEGIN
    UPDATE Medecin SET specialite = v_spec WHERE medecin_id = v_id;
    DBMS_OUTPUT.PUT_LINE('Spécialité mise à jour.');
END;
/

-- CRÉATION VUE DES MÉDECINS & LEURS SPÉCIALITÉS
CREATE VIEW vue_medecins AS
SELECT nom, specialite, email FROM Medecin;

-- INDEX SUR L'EMAIL DES PATIENTS
CREATE INDEX idx_patient_email ON Patient(email);
