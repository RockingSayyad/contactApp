<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacts</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">

    <!-- jQuery (necessary for DataTables) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

    <style>
        .modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            z-index: 1000;
        }

        .modal button {
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <h1>Contacts</h1>

    <hr>

    <h2>Import Contacts from XML</h2>

    <form action="{{ route('contacts.import') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="xml_file" id="xml_file" accept=".xml" required>
        <button type="submit">Import XML</button>
    </form>

    <hr>

    <h2>Contact List</h2>
    <table id="contactTable" class="display">
        <thead>
            <tr>
                <th>Name</th>
                <th>Phone</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>

    <!-- Add/Edit Contact Modal -->
    <div id="contactModal" class="modal">
        <form id="contactForm">
            @csrf
            <input type="hidden" name="id" id="contactId">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required>
            <label for="phone">Phone:</label>
            <input type="text" id="phone" name="phone" required>
            <button type="submit">Save</button>
        </form>
    </div>

    <script>
         $(document).ready(function() {
        table = $('#contactTable').DataTable({
            processing: true,
            serverSide: true,
            "searching": false ,
            ajax: {
                url: "{{ route('contacts.index') }}",
                type: 'GET',
                data: function(d) {
                    d.pageLength = d.length;
                }
            },
            columns: [{
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'phone',
                    name: 'phone'
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        return `
                <button class="edit-btn" data-id="${row.id}">Edit</button>
                <button class="delete-btn" data-id="${row.id}">Delete</button>`;
                    }
                }
            ],
            pagingType: 'full_numbers',
            pageLength: 10
        });

        $(document).on('click', '.edit-btn', function() {
            var contactId = $(this).data('id');
            if (contactId) {
                $.get('/contacts/' + contactId, function(data) {
                    $('#contactId').val(data.id);
                    $('#name').val(data.name);
                    $('#phone').val(data.phone);
                    $('#contactModal').show();
                });
            } else {
                $('#contactForm')[0].reset();
                $('#contactModal').show();
            }
        });

        $('#contactForm').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();

            $.ajax({
                url: '/contacts',
                type: 'POST',
                data: formData,
                success: function(response) {
                    $('#contactModal').hide();
                    table.ajax.reload();
                }
            });
        });

        $(document).on('click', '.delete-btn', function() {
            var contactId = $(this).data('id');
            if (confirm('Are you sure you want to delete this contact?')) {
                $.ajax({
                    url: '/contacts/' + contactId,
                    type: 'DELETE',
                    success: function(response) {
                        table.ajax.reload(); // Now this should work
                    },
                    error: function(xhr) {
                        alert('Error deleting contact: ' + xhr.responseText);
                    }
                });
            }
        });
    });

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
</script>

</body>

</html>