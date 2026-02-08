using System;
using System.Collections.Generic;
using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace CRM_School.Models
{
    // Shared Status Table
    [Table("Status")]
    public class Status
    {
        [Key]
        public int Id { get; set; }

        [Required]
        [StringLength(50)]
        public string StatusName { get; set; } = null!; // Mapped to column 'Status'

        // Navigation properties (Inverse)
        public ICollection<AcademicYear> AcademicYears { get; set; } = new List<AcademicYear>();
        public ICollection<Branch> Branches { get; set; } = new List<Branch>();
        public ICollection<SchoolClass> Classes { get; set; } = new List<SchoolClass>();
        public ICollection<Grade> Grades { get; set; } = new List<Grade>();
        public ICollection<Level> Levels { get; set; } = new List<Level>();
        public ICollection<ProductCategory> ProductCategories { get; set; } = new List<ProductCategory>();
        public ICollection<Room> Rooms { get; set; } = new List<Room>();
        public ICollection<Shift> Shifts { get; set; } = new List<Shift>();
        public ICollection<Student> Students { get; set; } = new List<Student>();
        public ICollection<User> Users { get; set; } = new List<User>();
    }

    [Table("AcademicYearStatus")]
    public class AcademicYearStatus
    {
        [Key]
        public int Id { get; set; }
        [Required]
        [StringLength(30)]
        public string Name { get; set; } = null!;
        
        public ICollection<AcademicYear> AcademicYears { get; set; } = new List<AcademicYear>();
    }

    [Table("AcademicYears")]
    public class AcademicYear
    {
        [Key]
        public int Id { get; set; }
        [Required]
        [StringLength(50)]
        public string Name { get; set; } = null!;
        public int StatusId { get; set; }

        [ForeignKey("StatusId")]
        public AcademicYearStatus AcademicYearStatus { get; set; } = null!; 

        public ICollection<SchoolClass> Classes { get; set; } = new List<SchoolClass>();
    }

    [Table("Branches")]
    public class Branch
    {
        [Key]
        public int Id { get; set; }
        [Required]
        [StringLength(100)]
        public string Name { get; set; } = null!;
        public int StatusId { get; set; }

        [ForeignKey("StatusId")]
        public Status Status { get; set; } = null!;

        public ICollection<SchoolClass> Classes { get; set; } = new List<SchoolClass>();
        public ICollection<Sale> Sales { get; set; } = new List<Sale>();
    }

    [Table("Levels")]
    public class Level
    {
        [Key]
        public int Id { get; set; }
        [Required]
        [StringLength(100)]
        public string Name { get; set; } = null!;
        public int StatusId { get; set; }

        [ForeignKey("StatusId")]
        public Status Status { get; set; } = null!;

        public ICollection<Grade> Grades { get; set; } = new List<Grade>();
    }

    [Table("Grades")]
    public class Grade
    {
        [Key]
        public int Id { get; set; }
        [Required]
        [StringLength(100)]
        public string Name { get; set; } = null!;
        public int LevelId { get; set; }
        public int StatusId { get; set; }

        [ForeignKey("LevelId")]
        public Level Level { get; set; } = null!;
        [ForeignKey("StatusId")]
        public Status Status { get; set; } = null!;

        public ICollection<SchoolClass> Classes { get; set; } = new List<SchoolClass>();
    }

    [Table("Shifts")]
    public class Shift
    {
        [Key]
        public int Id { get; set; }
        [Required]
        [StringLength(100)]
        public string Name { get; set; } = null!;
        public int StatusId { get; set; }

        [ForeignKey("StatusId")]
        public Status Status { get; set; } = null!;

        public ICollection<SchoolClass> Classes { get; set; } = new List<SchoolClass>();
    }

    [Table("Rooms")]
    public class Room
    {
        [Key]
        public int Id { get; set; }
        [Required]
        [StringLength(50)]
        public string Name { get; set; } = null!;
        public int StatusId { get; set; }

        [ForeignKey("StatusId")]
        public Status Status { get; set; } = null!;

        public ICollection<SchoolClass> Classes { get; set; } = new List<SchoolClass>();
    }

    [Table("Classes")]
    public class SchoolClass
    {
        [Key]
        public int Id { get; set; }
        [Required]
        [StringLength(50)]
        public string Name { get; set; } = null!;
        public int GradeId { get; set; }
        public int ShiftId { get; set; }
        public int? RoomId { get; set; }
        public int BranchId { get; set; }
        public int YearId { get; set; } 
        public int StatusId { get; set; }

        [ForeignKey("GradeId")]
        public Grade Grade { get; set; } = null!;
        [ForeignKey("ShiftId")]
        public Shift Shift { get; set; } = null!;
        [ForeignKey("RoomId")]
        public Room? Room { get; set; }
        [ForeignKey("BranchId")]
        public Branch Branch { get; set; } = null!;
        [ForeignKey("YearId")]
        public AcademicYear AcademicYear { get; set; } = null!;
        [ForeignKey("StatusId")]
        public Status Status { get; set; } = null!;

        public ICollection<Student> Students { get; set; } = new List<Student>();
    }

    [Table("Gender")]
    public class Gender
    {
        [Key]
        public int Id { get; set; }
        [Required]
        [StringLength(10)]
        public string GenderName { get; set; } = null!; // Column is 'Gender'

        public ICollection<Student> Students { get; set; } = new List<Student>();
    }

    [Table("Students")]
    public class Student
    {
        [Key]
        public int Id { get; set; }
        [Required]
        [StringLength(30)]
        public string StudentID { get; set; } = null!;
        [StringLength(150)]
        public string? NameLatin { get; set; }
        [StringLength(150)]
        public string? NameKhmer { get; set; }
        public int GenderId { get; set; }
        public int ClassId { get; set; }
        public DateTime? DOB { get; set; }
        public int StatusId { get; set; }

        [ForeignKey("GenderId")]
        public Gender Gender { get; set; } = null!;
        [ForeignKey("ClassId")]
        public SchoolClass Class { get; set; } = null!;
        [ForeignKey("StatusId")]
        public Status Status { get; set; } = null!;

        public ICollection<Payment> Payments { get; set; } = new List<Payment>();
        public ICollection<Sale> Sales { get; set; } = new List<Sale>();
    }

    [Table("Users")]
    public class User
    {
        [Key]
        public int Id { get; set; }
        [Required]
        [StringLength(100)]
        public string Username { get; set; } = null!;
        [Required]
        [StringLength(255)]
        public string Password { get; set; } = null!;
        [StringLength(100)]
        public string? Position { get; set; }
        public int StatusId { get; set; }

        [ForeignKey("StatusId")]
        public Status Status { get; set; } = null!;

        public ICollection<Payment> Payments { get; set; } = new List<Payment>();
        public ICollection<Sale> Sales { get; set; } = new List<Sale>();
    }

    [Table("Payments")]
    public class Payment
    {
        [Key]
        public int PaymentID { get; set; }
        [Required]
        [StringLength(50)]
        public string ReceiptNo { get; set; } = null!;
        public DateTime Date { get; set; }
        [StringLength(30)]
        public string? StudentID { get; set; }
        [Required]
        [StringLength(100)]
        public string FeeType { get; set; } = null!;
        public decimal Amount { get; set; }
        public int UserID { get; set; }
        [StringLength(100)]
        public string? Period { get; set; }

        [ForeignKey("StudentID")]
        public Student? Student { get; set; }
        [ForeignKey("UserID")]
        public User User { get; set; } = null!;
    }

    [Table("ProductCategories")]
    public class ProductCategory
    {
        [Key]
        public int Id { get; set; }
        [Required]
        [StringLength(150)]
        public string Name { get; set; } = null!;
        public int StatusId { get; set; }

        [ForeignKey("StatusId")]
        public Status Status { get; set; } = null!;
        public ICollection<Product> Products { get; set; } = new List<Product>();
    }

    [Table("Products")]
    public class Product
    {
        [Key]
        public int Id { get; set; }
        [Required]
        [StringLength(200)]
        public string Name { get; set; } = null!;
        public int CategoryId { get; set; }
        public decimal UnitPrice { get; set; }

        [ForeignKey("CategoryId")]
        public ProductCategory Category { get; set; } = null!;
        public ICollection<SaleDetail> SaleDetails { get; set; } = new List<SaleDetail>();
    }

    [Table("Sales")]
    public class Sale
    {
        [Key]
        public int Id { get; set; }
        [Required]
        [StringLength(50)]
        public string ReceiptNo { get; set; } = null!;
        public DateTime Date { get; set; }
        [StringLength(30)]
        public string? StudentID { get; set; }
        public int UserID { get; set; }
        public int BranchID { get; set; }

        [ForeignKey("StudentID")]
        public Student? Student { get; set; }
        [ForeignKey("UserID")]
        public User User { get; set; } = null!;
        [ForeignKey("BranchID")]
        public Branch Branch { get; set; } = null!;
        
        public ICollection<SaleDetail> SaleDetails { get; set; } = new List<SaleDetail>();
    }

    [Table("SaleDetails")]
    public class SaleDetail
    {
        [Key]
        public int DetailID { get; set; }
        public int SaleID { get; set; }
        public int ProductID { get; set; }
        public int Qty { get; set; }
        public decimal UnitPrice { get; set; }
        public decimal LineTotal { get; set; }

        [ForeignKey("SaleID")]
        public Sale Sale { get; set; } = null!;
        [ForeignKey("ProductID")]
        public Product Product { get; set; } = null!;
    }
}
