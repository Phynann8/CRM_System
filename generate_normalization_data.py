import pandas as pd

# 1. Employee Data
employee_data = {
    'EmployeeID': [101, 102, 103, 104, 105, 106],
    'FullName': ["John Doe", "Jane Smith", "Robert Brown", "Emily Davis", "Michael Wilson", "Sarah Johnson"],
    'Address': ["123 Maple St, New York, NY", "456 Oak Ave, Boston, MA", "789 Pine Rd, Chicago, IL", "321 Elm St, New York, NY", "654 Cedar Ln, Boston, MA", "987 Birch Blvd, Austin, TX"],
    'Department': ["IT - Development", "HR - Recruitment", "IT - Development", "Marketing - Sales", "IT - Support", "IT - Development"],
    'Role': ["Developer", "Recruiter", "Senior Dev", "Manager", "Support Specialist", "Developer"],
    'ProjectAssignments': ["ProjA, ProjB", "ProjC", "ProjA, ProjD", "ProjE", "ProjF", "ProjB, ProjG"],
    'Skills': ["Java, SQL, Python", "Communication, Excel", "C#, Azure, .NET", "SEO, CRM", "Linux, Troubleshooting", "Python, React, AWS"],
    'Salary': [85000, 65000, 95000, 75000, 55000, 88000]
}
df_employee = pd.DataFrame(employee_data)

# 2. Stock and Product Sales Data
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
df_stock = pd.DataFrame(stock_data)

# 3. Academic / School Information
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
df_academic = pd.DataFrame(academic_data)

# Write to Excel
output_file = 'e:/Website_Development/CRM_System/Normalization_Practice_Data.xlsx'
with pd.ExcelWriter(output_file, engine='openpyxl') as writer:
    df_employee.to_excel(writer, sheet_name='Employee_Raw', index=False)
    df_stock.to_excel(writer, sheet_name='Stock_Sales_Raw', index=False)
    df_academic.to_excel(writer, sheet_name='Academic_Info_Raw', index=False)

print(f"Successfully created {output_file}")
