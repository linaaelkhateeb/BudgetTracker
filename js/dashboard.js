src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"

        // Initialize data if not exists
        function initializeData() {
            if (!localStorage.getItem('budgetData')) {
                const initialData = {
                    income: 4250,
                    expenses: 2840,
                    balance: 1410,
                    transactions: [
                        { id: 1, type: 'income', amount: 1200, category: 'Salary', description: 'Monthly Salary', date: '2023-06-15' },
                        { id: 2, type: 'expense', amount: 350, category: 'Housing', description: 'Rent Payment', date: '2023-06-10' },
                        { id: 3, type: 'expense', amount: 120, category: 'Utilities', description: 'Electricity Bill', date: '2023-06-08' },
                        { id: 4, type: 'expense', amount: 85, category: 'Food', description: 'Grocery Shopping', date: '2023-06-05' },
                        { id: 5, type: 'income', amount: 300, category: 'Freelance', description: 'Web Design Project', date: '2023-06-03' }
                    ],
                    budgets: [
                        { category: 'Housing', spent: 350, limit: 500, percentage: 70 },
                        { category: 'Food', spent: 320, limit: 400, percentage: 80 },
                        { category: 'Transportation', spent: 180, limit: 250, percentage: 72 }
                    ]
                };
                localStorage.setItem('budgetData', JSON.stringify(initialData));
            }
        }

        // Scroll animations
        function checkScroll() {
            const fadeElements = document.querySelectorAll('.fade-in');
            
            fadeElements.forEach(element => {
                const elementTop = element.getBoundingClientRect().top;
                const elementVisible = 150;
                
                if (elementTop < window.innerHeight - elementVisible) {
                    element.classList.add('visible');
                }
            });
            
            // Navbar effect
            if (window.scrollY > 50) {
                document.querySelector('.navbar').classList.add('scrolled');
            } else {
                document.querySelector('.navbar').classList.remove('scrolled');
            }
        }

        // Populate category options based on transaction type
        function populateCategories(type) {
            const categorySelect = document.getElementById('transactionCategory');
            categorySelect.innerHTML = '';
            
            const categories = {
                income: ['Salary', 'Freelance', 'Investment', 'Gift', 'Other'],
                expense: ['Housing', 'Food', 'Transportation', 'Utilities', 'Entertainment', 'Healthcare', 'Other']
            };
            
            categories[type].forEach(category => {
                const option = document.createElement('option');
                option.value = category;
                option.textContent = category;
                categorySelect.appendChild(option);
            });
        }

        // Save transaction
        function saveTransaction() {
            const type = document.getElementById('transactionType').value;
            const amount = parseFloat(document.getElementById('transactionAmount').value);
            const category = document.getElementById('transactionCategory').value;
            const description = document.getElementById('transactionDescription').value || 'No description';
            
            if (!amount || amount <= 0) {
                alert('Please enter a valid amount');
                return;
            }
            
            const data = JSON.parse(localStorage.getItem('budgetData'));
            
            // Add new transaction
            const newTransaction = {
                id: Date.now(),
                type,
                amount,
                category,
                description,
                date: new Date().toISOString().split('T')[0]
            };
            
            data.transactions.unshift(newTransaction);
            
            // Update totals
            if (type === 'income') {
                data.income += amount;
                data.balance += amount;
            } else {
                data.expenses += amount;
                data.balance -= amount;
            }
            
            // Update budget if it's an expense
            if (type === 'expense') {
                const budget = data.budgets.find(b => b.category === category);
                if (budget) {
                    budget.spent += amount;
                    budget.percentage = Math.min(100, Math.round((budget.spent / budget.limit) * 100));
                }
            }
            
            localStorage.setItem('budgetData', JSON.stringify(data));
            
            // Close modal and reload data
            const modal = bootstrap.Modal.getInstance(document.getElementById('transactionModal'));
            modal.hide();
            location.reload(); // Simple reload for demo purposes
        }

        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            initializeData();
            
            // Set up scroll listener
            window.addEventListener('scroll', checkScroll);
            checkScroll(); // Check on initial load
            
            // Set up event listeners for quick action buttons
            document.getElementById('add-income').addEventListener('click', function() {
                document.getElementById('modalTitle').textContent = 'Add Income';
                document.getElementById('transactionType').value = 'income';
                populateCategories('income');
                document.getElementById('transactionAmount').value = '';
                document.getElementById('transactionDescription').value = '';
                
                const modal = new bootstrap.Modal(document.getElementById('transactionModal'));
                modal.show();
            });
            
            document.getElementById('add-expense').addEventListener('click', function() {
                document.getElementById('modalTitle').textContent = 'Add Expense';
                document.getElementById('transactionType').value = 'expense';
                populateCategories('expense');
                document.getElementById('transactionAmount').value = '';
                document.getElementById('transactionDescription').value = '';
                
                const modal = new bootstrap.Modal(document.getElementById('transactionModal'));
                modal.show();
            });
            
            // Set up event listener for transaction type change
            document.getElementById('transactionType').addEventListener('change', function() {
                populateCategories(this.value);
            });
            
            // Set up event listener for save transaction button
            document.getElementById('saveTransaction').addEventListener('click', saveTransaction);
            
            // Smooth scrolling for navigation links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        window.scrollTo({
                            top: target.offsetTop - 80,
                            behavior: 'smooth'
                        });
                    }
                });
            });
        });
   