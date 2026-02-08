# Raw Data for Database Normalization Practice

Here are three datasets provided in raw, un-normalized format (CSV). They contain redundant data, multi-valued attributes, and transitive dependencies, making them suitable for practicing 1NF, 2NF, and 3NF normalization.

## 1. Employee Data (Un-normalized)
**Issues to fix:**
*   **Multi-valued attributes:** `Skills` contains multiple values.
*   **Composite attributes:** `Address` contains street, city, and zip.
*   **Redundancy:** Department info repeats.

```csv
EmployeeID,FullName,Address,Department,Role,ProjectAssignments,Skills,Salary
101,"John Doe","123 Maple St, New York, NY","IT - Development","Developer","ProjA, ProjB","Java, SQL, Python",85000
102,"Jane Smith","456 Oak Ave, Boston, MA","HR - Recruitment","Recruiter","ProjC","Communication, Excel",65000
103,"Robert Brown","789 Pine Rd, Chicago, IL","IT - Development","Senior Dev","ProjA, ProjD","C#, Azure, .NET",95000
104,"Emily Davis","321 Elm St, New York, NY","Marketing - Sales","Manager","ProjE","SEO, CRM",75000
105,"Michael Wilson","654 Cedar Ln, Boston, MA","IT - Support","Support Specialist","ProjF","Linux, Troubleshooting",55000
106,"Sarah Johnson","987 Birch Blvd, Austin, TX","IT - Development","Developer","ProjB, ProjG","Python, React, AWS",88000
```

---

## 2. Stock and Product Sales Data (Un-normalized)
**Issues to fix:**
*   **Transitive Dependencies:** `SupplierContact` depends on `SupplierName`. `CategoryDescription` depends on `Category`.
*   **Calculated Fields:** `Total` is redundant.
*   **Repeating Groups:** Sales information mixed with product information.

```csv
InvoiceNum,Date,CustomerName,ProductID,ProductName,Category,CategoryDesc,Supplier,SupplierContact,Qty,UnitPrice,Total
INV-001,2025-01-10,"Acme Corp",P001,"Laptop X1","Electronics","Gadgets and devices","TechSource","contact@techsource.com",2,1200,2400
INV-001,2025-01-10,"Acme Corp",P005,"Mouse Wireless","Accessories","Peripheral devices","GearSupply","sales@gearsupply.com",5,25,125
INV-002,2025-01-11,"Globex Inc",P002,"Desktop Pro","Electronics","Gadgets and devices","TechSource","contact@techsource.com",1,1500,1500
INV-003,2025-01-12,"Soylent Corp",P003,"Office Chair","Furniture","Office furniture","FurniCo","support@furnico.com",10,150,1500
INV-003,2025-01-12,"Soylent Corp",P004,"Desk Lamp","Furniture","Office furniture","FurniCo","support@furnico.com",10,45,450
INV-004,2025-01-13,"Acme Corp",P001,"Laptop X1","Electronics","Gadgets and devices","TechSource","contact@techsource.com",1,1200,1200
```

---

## 3. Academic / School Information (Un-normalized)
**Issues to fix:**
*   **Mixed Entities:** Student, Teacher, Subject, Class, and Room information all in one table.
*   **Redundancy:** Teacher details repeat for every student in their class. Room info repeats.
*   **Multi-valued Fields:** `Subjects` (if treated as a list).

```csv
StudentID,StudentName,DOB,Grade,Class,Room,TeacherName,TeacherEmail,Subject,Schedule,Score
S001,"Alice White",2010-05-15,10,"10-A",101,"Mr. Anderson","anderson@school.edu","Math","Mon 09:00",85
S001,"Alice White",2010-05-15,10,"10-A",101,"Mr. Anderson","anderson@school.edu","History","Tue 10:00",90
S002,"Bob Green",2010-08-22,10,"10-A",101,"Mr. Anderson","anderson@school.edu","Math","Mon 09:00",78
S002,"Bob Green",2010-08-22,10,"10-A",101,"Mr. Anderson","anderson@school.edu","History","Tue 10:00",82
S003,"Charlie Black",2009-11-30,11,"11-B",104,"Ms. Roberts","roberts@school.edu","Physics","Wed 11:00",92
S003,"Charlie Black",2009-11-30,11,"11-B",104,"Ms. Roberts","roberts@school.edu","Chemistry","Thu 13:00",88
S004,"Diana Blue",2010-02-14,10,"10-A",101,"Mr. Anderson","anderson@school.edu","Math","Mon 09:00",95
S005,"Evan Gray",2011-01-10,9,"9-C",102,"Mrs. Clark","clark@school.edu","English","Fri 09:00",80
```
