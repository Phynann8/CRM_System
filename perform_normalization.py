import pandas as pd
import os
import re

# Define output path
output_file = 'e:/Website_Development/CRM_System/Normalized_Data_Full.xlsx'

# ==========================================
# 1. STUDENT FAMILY DATA SETUP (Replaces Employee)
# ==========================================
print("Processing Student Family Data...")
student_family_data = {
    'StudentID': ["ST001", "ST002", "ST003", "ST004", "ST005"],
    'FullName': ["Sok Dara", "Ly Vanna", "Chea Mony", "Heng Piseth", "Keo Bopha"],
    'DOB': ["2010-05-15", "2010-08-22", "2009-11-30", "2010-02-14", "2011-01-10"],
    'Gender': ["M", "F", "M", "M", "F"],
    'Address': [
        "No. 123, St. 200, Daun Penh, Phnom Penh, 12201", 
        "No. 45, St. 101, Toul Kork, Phnom Penh, 12152", 
        "No. 89, St. 360, Chamkar Mon, Phnom Penh, 12301", 
        "No. 123, St. 200, Daun Penh, Phnom Penh, 12201", # Same address as ST001 (Maybe siblings?)
        "No. 12, Village 3, Siem Reap City, Siem Reap, 17251"
    ],
    'ParentDetails': [
        "Father: Sok Visal - 012888999, Mother: Chan Thy - 012777666",
        "Mother: Ly Srey - 015222333",
        "Father: Chea Rith - 017444555",
        "Father: Sok Visal - 012888999, Mother: Chan Thy - 012777666", # Siblings with ST001
        "Father: Keo Tola - 092555666, Mother: Sim Sophea - 010999888"
    ],
    'Siblings': [
        "ST004", 
        "", 
        "", 
        "ST001", 
        ""
    ]
}
stud_unf = pd.DataFrame(student_family_data)

# --- 1NF ---
# Atomic Address: Split into HouseNo, Street, District, City, Zip
# Assuming format roughly: "No. X, St. Y, District, City, Zip"
# Using regex for simpler parsing or just string split if consistent
stud_1nf = stud_unf.copy()

def parse_address(addr):
    parts = [p.strip() for p in addr.split(',')]
    if len(parts) >= 5:
        return pd.Series([parts[0], parts[1], parts[2], parts[3], parts[4]], index=['House_Street', 'Street_Name', 'District', 'City', 'ZipCode'])
    else:
        return pd.Series([addr, "", "", "", ""], index=['House_Street', 'Street_Name', 'District', 'City', 'ZipCode'])

stud_1nf[['HouseNo', 'Street', 'District', 'City', 'ZipCode']] = stud_1nf['Address'].apply(parse_address)
stud_1nf = stud_1nf.drop(columns=['Address'])

# Atomic Parents (Separate Table)
# Split "Father: Name - Phone, Mother: Name - Phone"
parents_list = []
for idx, row in stud_1nf.iterrows():
    p_str = row['ParentDetails']
    if p_str:
        parents = p_str.split(', ')
        for p in parents:
            # Parse "Relation: Name - Phone"
            match = re.search(r'(.*?):\s*(.*?)\s*-\s*(.*)', p)
            if match:
                parents_list.append({
                    'StudentID': row['StudentID'],
                    'Relationship': match.group(1),
                    'ParentName': match.group(2),
                    'ParentPhone': match.group(3)
                })

stud_parents_1nf = pd.DataFrame(parents_list)

# Atomic Siblings (Separate Table)
siblings_list = []
for idx, row in stud_1nf.iterrows():
    s_str = row['Siblings']
    if s_str:
        sibs = s_str.split(', ')
        for s in sibs:
            if s:
                siblings_list.append({
                    'StudentID': row['StudentID'],
                    'SiblingStudentID': s
                })
stud_siblings_1nf = pd.DataFrame(siblings_list)

# Clean Main 1NF Table
stud_1nf_main = stud_1nf.drop(columns=['ParentDetails', 'Siblings'])

# --- 2NF ---
# Main table PK: StudentID. All atomic columns depend on it. (Already 2NF).
stud_2nf_main = stud_1nf_main.copy()
# Parents PK: (StudentID, ParentName) or (StudentID, Relationship).
# No partial dependencies for these composed keys.
stud_parents_2nf = stud_parents_1nf.copy()

# --- 3NF ---
# Main Table Transitive Dependencies: ZipCode -> City, District?
# Or ZipCode -> City, Province.
# Let's extract Location based on ZipCode.
locations = stud_2nf_main[['ZipCode', 'City', 'District']].drop_duplicates().reset_index(drop=True)
# If ZipCode is unique for district/city in this dataset
locations['LocationID'] = range(1, len(locations) + 1)

stud_3nf_main = stud_2nf_main.merge(locations, on=['ZipCode', 'City', 'District'])
stud_3nf_main = stud_3nf_main[['StudentID', 'FullName', 'DOB', 'Gender', 'HouseNo', 'Street', 'LocationID']]

locations_3nf = locations[['LocationID', 'District', 'City', 'ZipCode']]

# Parents Table: 
# Does ParentName -> Phone? Likely.
# Normalize Parents to their own entity.
unique_parents = stud_parents_2nf[['ParentName', 'ParentPhone']].drop_duplicates().reset_index(drop=True)
unique_parents['ParentID'] = range(1, len(unique_parents) + 1)

stud_parents_map_3nf = stud_parents_2nf.merge(unique_parents, on=['ParentName', 'ParentPhone'])
stud_parents_map_3nf = stud_parents_map_3nf[['StudentID', 'ParentID', 'Relationship']]
parents_3nf = unique_parents[['ParentID', 'ParentName', 'ParentPhone']]

# Siblings can map Student to Student directly
stud_siblings_3nf = stud_siblings_1nf.copy()


# ==========================================
# 2. STOCK DATA SETUP (Unchanged)
# ==========================================
print("Processing Stock Data...")
stock_data = {
    'InvoiceNum': ["INV-001", "INV-001", "INV-002", "INV-003", "INV-003", "INV-004"],
    'Date': ["2025-01-10", "2025-01-10", "2025-01-11", "2025-01-12", "2025-01-12", "2025-01-13"],
    'CustomerName': ["Acme Corp", "Acme Corp", "Globex Inc", "Soylent Corp", "Soylent Corp", "Acme Corp"],
    'ProductID': ["P001", "P005", "P002", "P003", "P004", "P001"],
    'ProductName': ["Laptop X1", "Mouse Wireless", "Desktop Pro", "Office Chair", "Desk Lamp", "Laptop X1"],
    'Category': ["Electronics", "Accessories", "Electronics", "Furniture", "Furniture", "Electronics"],
    'CategoryDesc': ["Gadgets and devices", "Peripheral devices", "Gadgets and devices", "Office furniture", "Office furniture", "Gadgets and devices"],
    'Supplier': ["TechSource", "GearSupply", "TechSource", "FurniCo", "FurniCo", "TechSource"],
    'SupplierContact': ["contact@techsource.com", "sales@gearsupply.com", "contact@techsource.com", "support@furnico.com", "support@furnico.com", "contact@techsource.com"],
    'Qty': [2, 5, 1, 10, 10, 1],
    'UnitPrice': [1200, 25, 1500, 150, 45, 1200],
    'Total': [2400, 125, 1500, 1500, 450, 1200]
}
stock_unf = pd.DataFrame(stock_data)

# Stock Logic (Same as before)
stock_1nf = stock_unf.copy()
stock_invoices_2nf = stock_1nf[['InvoiceNum', 'Date', 'CustomerName']].drop_duplicates()
stock_products_2nf = stock_1nf[['ProductID', 'ProductName', 'Category', 'CategoryDesc', 'Supplier', 'SupplierContact', 'UnitPrice']].drop_duplicates()
stock_items_2nf = stock_1nf[['InvoiceNum', 'ProductID', 'Qty']]
cats = stock_products_2nf[['Category', 'CategoryDesc']].drop_duplicates().reset_index(drop=True)
cats['CategoryID'] = range(1, len(cats) + 1)
supps = stock_products_2nf[['Supplier', 'SupplierContact']].drop_duplicates().reset_index(drop=True)
supps['SupplierID'] = range(1, len(supps) + 1)
products_3nf = stock_products_2nf.merge(cats, on=['Category', 'CategoryDesc']).merge(supps, on=['Supplier', 'SupplierContact'])
products_3nf = products_3nf[['ProductID', 'ProductName', 'UnitPrice', 'CategoryID', 'SupplierID']]
categories_3nf = cats[['CategoryID', 'Category', 'CategoryDesc']]
suppliers_3nf = supps[['SupplierID', 'Supplier', 'SupplierContact']]
custs = stock_invoices_2nf[['CustomerName']].drop_duplicates().reset_index(drop=True)
custs['CustomerID'] = range(1, len(custs) + 1)
invoices_3nf = stock_invoices_2nf.merge(custs, on='CustomerName')
invoices_3nf = invoices_3nf[['InvoiceNum', 'Date', 'CustomerID']]
customers_3nf = custs[['CustomerID', 'CustomerName']]
items_3nf = stock_items_2nf.copy()

# ==========================================
# 3. ACADEMIC DATA SETUP (Unchanged)
# ==========================================
print("Processing Academic Data...")
academic_data = {
    'StudentID': ["S001", "S001", "S002", "S002", "S003", "S003", "S004", "S005"],
    'StudentName': ["Alice White", "Alice White", "Bob Green", "Bob Green", "Charlie Black", "Charlie Black", "Diana Blue", "Evan Gray"],
    'DOB': ["2010-05-15", "2010-05-15", "2010-08-22", "2010-08-22", "2009-11-30", "2009-11-30", "2010-02-14", "2011-01-10"],
    'Grade': [10, 10, 10, 10, 11, 11, 10, 9],
    'Class': ["10-A", "10-A", "10-A", "10-A", "11-B", "11-B", "10-A", "9-C"],
    'Room': [101, 101, 101, 101, 104, 104, 101, 102],
    'TeacherName': ["Mr. Anderson", "Mr. Anderson", "Mr. Anderson", "Mr. Anderson", "Ms. Roberts", "Ms. Roberts", "Mr. Anderson", "Mrs. Clark"],
    'TeacherEmail': ["anderson@school.edu", "anderson@school.edu", "anderson@school.edu", "anderson@school.edu", "roberts@school.edu", "roberts@school.edu", "anderson@school.edu", "clark@school.edu"],
    'Subject': ["Math", "History", "Math", "History", "Physics", "Chemistry", "Math", "English"],
    'Schedule': ["Mon 09:00", "Tue 10:00", "Mon 09:00", "Tue 10:00", "Wed 11:00", "Thu 13:00", "Mon 09:00", "Fri 09:00"],
    'Score': [85, 90, 78, 82, 92, 88, 95, 80]
}
acad_unf = pd.DataFrame(academic_data)

# Academic Logic (Same as before)
acad_1nf = acad_unf.copy()
col_student = ['StudentID', 'StudentName', 'DOB', 'Class', 'Grade', 'Room'] 
students_2nf = acad_1nf[col_student].drop_duplicates()
scores_schedule_2nf = acad_1nf[['StudentID', 'Subject', 'TeacherName', 'TeacherEmail', 'Schedule', 'Score']]
classes = students_2nf[['Class', 'Room', 'Grade']].drop_duplicates().reset_index(drop=True)
classes['ClassID'] = range(1, len(classes)+1)
students_3nf = students_2nf.merge(classes, on=['Class', 'Room', 'Grade'])
students_3nf = students_3nf[['StudentID', 'StudentName', 'DOB', 'ClassID']]
classes_3nf = classes[['ClassID', 'Class', 'Room', 'Grade']]
teachers = acad_1nf[['TeacherName', 'TeacherEmail']].drop_duplicates().reset_index(drop=True)
teachers['TeacherID'] = range(1, len(teachers)+1)
subjects = acad_1nf[['Subject']].drop_duplicates().reset_index(drop=True)
subjects['SubjectID'] = range(1, len(subjects)+1)
scores_3nf_raw = acad_1nf.merge(teachers, on=['TeacherName', 'TeacherEmail']).merge(subjects, on='Subject')
scores_3nf = scores_3nf_raw[['StudentID', 'SubjectID', 'Score', 'TeacherID', 'Schedule']] 
teachers_3nf = teachers[['TeacherID', 'TeacherName', 'TeacherEmail']]
subjects_3nf = subjects[['SubjectID', 'Subject']]

# ==========================================
# WRITE TO EXCEL
# ==========================================
print(f"Writing to {output_file}...")
with pd.ExcelWriter(output_file, engine='openpyxl') as writer:
    # Student Family
    stud_unf.to_excel(writer, sheet_name='Stud_Fam_UNF', index=False)
    stud_1nf_main.to_excel(writer, sheet_name='Stud_1NF_Main', index=False)
    stud_parents_1nf.to_excel(writer, sheet_name='Stud_1NF_Parents', index=False)
    stud_siblings_1nf.to_excel(writer, sheet_name='Stud_1NF_Siblings', index=False)
    stud_3nf_main.to_excel(writer, sheet_name='Stud_3NF_Main', index=False)
    locations_3nf.to_excel(writer, sheet_name='Stud_3NF_Location', index=False)
    stud_parents_map_3nf.to_excel(writer, sheet_name='Stud_3NF_ParMap', index=False)
    parents_3nf.to_excel(writer, sheet_name='Stud_3NF_Parents', index=False)
    
    # Stock
    stock_unf.to_excel(writer, sheet_name='Stock_UNF', index=False)
    stock_products_2nf.to_excel(writer, sheet_name='Stock_2NF_Prod', index=False)
    stock_invoices_2nf.to_excel(writer, sheet_name='Stock_2NF_Inv', index=False)
    products_3nf.to_excel(writer, sheet_name='Stock_3NF_Prod', index=False)
    categories_3nf.to_excel(writer, sheet_name='Stock_3NF_Cats', index=False)
    suppliers_3nf.to_excel(writer, sheet_name='Stock_3NF_Supp', index=False)
    customers_3nf.to_excel(writer, sheet_name='Stock_3NF_Cust', index=False)
    invoices_3nf.to_excel(writer, sheet_name='Stock_3NF_Inv', index=False)
    items_3nf.to_excel(writer, sheet_name='Stock_3NF_Items', index=False)

    # Academic
    acad_unf.to_excel(writer, sheet_name='Acad_UNF', index=False)
    students_3nf.to_excel(writer, sheet_name='Acad_3NF_Stud', index=False)
    classes_3nf.to_excel(writer, sheet_name='Acad_3NF_Class', index=False)
    teachers_3nf.to_excel(writer, sheet_name='Acad_3NF_Teach', index=False)
    subjects_3nf.to_excel(writer, sheet_name='Acad_3NF_Subj', index=False)
    scores_3nf.to_excel(writer, sheet_name='Acad_3NF_Scores', index=False)

print("Done.")
