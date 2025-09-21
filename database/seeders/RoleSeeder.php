<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'Super Admin',
            'Admin',
            'Moderator',
            'Editor',
            'Content Manager',
            'Content Creator',
            'SEO Specialist',
            'Marketing Manager',
            'Sales Manager',
            'Support Agent',
            'Customer Success',
            'Finance Manager',
            'Accountant',
            'HR Manager',
            'Recruiter',
            'Trainer',
            'Employee',
            'Manager',
            'Supervisor',
            'Team Lead',
            'Developer',
            'Frontend Developer',
            'Backend Developer',
            'Fullstack Developer',
            'DevOps Engineer',
            'QA Tester',
            'UI UX Designer',
            'Product Manager',
            'Project Manager',
            'Business Analyst',
            'Data Analyst',
            'Data Scientist',
            'AI Engineer',
            'Security Analyst',
            'IT Support',
            'System Administrator',
            'Network Engineer',
            'Database Administrator',
            'Intern',
            'Junior Developer',
            'Senior Developer',
            'Tech Lead',
            'Engineering Manager',
            'Creative Director',
            'Video Editor',
            'Photographer',
            'Graphic Designer',
            'Illustrator',
            'Brand Manager',
            'Social Media Manager',
            'Community Manager',
            'Legal Advisor',
            'Compliance Officer',
            'Procurement Officer',
            'Operations Manager',
            'Logistics Manager',
            'Warehouse Staff',
            'Delivery Driver',
            'Field Agent',
            'Store Manager',
            'Cashier',
            'Customer Service Manager',
            'Technical Writer',
            'Product Owner',
            'Scrum Master',
            'Investor',
            'Partner',
            'Advisor',
            'Consultant',
            'Freelancer',
            'Client',
            'Vendor',
            'Supplier',
            'Buyer',
            'Auditor',
            'Executive Assistant',
            'Personal Assistant',
            'Chief Executive Officer',
            'Chief Operating Officer',
            'Chief Financial Officer',
            'Chief Marketing Officer',
            'Chief Technology Officer',
            'Chairman',
            'Vice President',
            'President',
            'Board Member',
            'Volunteer',
            'Donor',
            'Member',
            'Subscriber',
            'Resident',
            'Visitor',
            'Guest',
            'Applicant',
            'Candidate',
            'Mentor',
            'Mentee',
            'Facilitator',
            'Coordinator',
        ];

        foreach ($roles as $roleName) {
            $slug = Str::slug($roleName); // convert to kebab-case
            Role::firstOrCreate(['name' => $slug]);
        }
    }
}
