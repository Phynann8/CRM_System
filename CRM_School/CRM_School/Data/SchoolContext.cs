using System;
using Microsoft.EntityFrameworkCore;
using CRM_School.Models;

namespace CRM_School.Data
{
    public class SchoolContext : DbContext
    {
        public DbSet<AcademicYear> AcademicYears { get; set; } = null!;
        public DbSet<AcademicYearStatus> AcademicYearStatuses { get; set; } = null!;
        public DbSet<Branch> Branches { get; set; } = null!;
        public DbSet<SchoolClass> Classes { get; set; } = null!;
        public DbSet<Gender> Genders { get; set; } = null!;
        public DbSet<Grade> Grades { get; set; } = null!;
        public DbSet<Level> Levels { get; set; } = null!;
        public DbSet<Payment> Payments { get; set; } = null!;
        public DbSet<ProductCategory> ProductCategories { get; set; } = null!;
        public DbSet<Product> Products { get; set; } = null!;
        public DbSet<Room> Rooms { get; set; } = null!;
        public DbSet<SaleDetail> SaleDetails { get; set; } = null!;
        public DbSet<Sale> Sales { get; set; } = null!;
        public DbSet<Shift> Shifts { get; set; } = null!;
        public DbSet<Status> Statuses { get; set; } = null!;
        public DbSet<Student> Students { get; set; } = null!;
        public DbSet<User> Users { get; set; } = null!;

        protected override void OnConfiguring(DbContextOptionsBuilder optionsBuilder)
        {
            if (!optionsBuilder.IsConfigured)
            {
                optionsBuilder.UseSqlServer("Server=.;Database=CRM_School;Trusted_Connection=True;TrustServerCertificate=True;");
            }
        }

        protected override void OnModelCreating(ModelBuilder modelBuilder)
        {
            modelBuilder.Entity<Payment>()
                .HasIndex(p => p.ReceiptNo)
                .IsUnique();

            modelBuilder.Entity<Sale>()
                .HasIndex(s => s.ReceiptNo)
                .IsUnique();

            modelBuilder.Entity<Student>()
                .HasIndex(s => s.StudentID)
                .IsUnique();

            modelBuilder.Entity<User>()
                .HasIndex(u => u.Username)
                .IsUnique();

            modelBuilder.Entity<Gender>()
                .Property(g => g.GenderName)
                .HasColumnName("Gender");

             modelBuilder.Entity<Status>()
                .Property(s => s.StatusName)
                .HasColumnName("Status");
                
            modelBuilder.Entity<Payment>()
                .HasOne(p => p.Student)
                .WithMany(s => s.Payments)
                .HasForeignKey(p => p.StudentID)
                .HasPrincipalKey(s => s.StudentID);

            modelBuilder.Entity<Sale>()
                .HasOne(s => s.Student)
                .WithMany(st => st.Sales)
                .HasForeignKey(s => s.StudentID)
                .HasPrincipalKey(st => st.StudentID);
        }
    }
}
