  // Filter wishlist
  function filterWishlist() {
      const continent = document.getElementById('filterContinent').value;
      const category = document.getElementById('filterCategory').value;
      const search = document.getElementById('searchWishlist').value.toLowerCase();

      const cards = document.querySelectorAll('.wishlist-card');
      let visibleCount = 0;

      cards.forEach(card => {
          const cardContinent = card.dataset.continent;
          const cardCategory = card.dataset.category;
          const cardName = card.dataset.name;

          const matchContinent = continent === 'all' || cardContinent === continent;
          const matchCategory = category === 'all' || cardCategory === category;
          const matchSearch = search === '' || cardName.includes(search);

          if (matchContinent && matchCategory && matchSearch) {
              card.style.display = 'block';
              visibleCount++;
          } else {
              card.style.display = 'none';
          }
      });

      if (visibleCount === 0 && (continent !== 'all' || category !== 'all' || search !== '')) {
          Swal.fire({
              title: 'No Results',
              text: 'No destinations match your filters',
              icon: 'info',
              confirmButtonColor: '#c9a96e'
          });
      }
  }

  // Remove from wishlist
  async function removeFromWishlist(destinationId, destinationName) {
      const result = await Swal.fire({
          title: 'Remove from Wishlist?',
          text: `Remove ${destinationName} from your wishlist?`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#e74c3c',
          cancelButtonColor: '#6b5b4f',
          confirmButtonText: 'Yes, remove it',
          cancelButtonText: 'Cancel'
      });

      if (result.isConfirmed) {
          try {
              const response = await fetch(`/wishlist/${destinationId}`, {
                  method: 'DELETE',
                  headers: {
                      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                      'Content-Type': 'application/json'
                  }
              });

              const data = await response.json();

              if (data.success) {
                  Swal.fire({
                      title: 'Removed!',
                      text: data.message,
                      icon: 'success',
                      confirmButtonColor: '#c9a96e',
                      timer: 2000
                  }).then(() => {
                      window.location.reload();
                  });
              } else {
                  throw new Error(data.message);
              }
          } catch (error) {
              Swal.fire({
                  title: 'Error',
                  text: 'Failed to remove from wishlist',
                  icon: 'error',
                  confirmButtonColor: '#c9a96e'
              });
          }
      }
  }

  // Clear all wishlist
  async function clearAllWishlist() {
      const result = await Swal.fire({
          title: 'Clear All?',
          text: 'This will remove all destinations from your wishlist',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#e74c3c',
          cancelButtonColor: '#6b5b4f',
          confirmButtonText: 'Yes, clear all',
          cancelButtonText: 'Cancel'
      });

      if (result.isConfirmed) {
          // In production, make API call to clear all
          Swal.fire({
              title: 'Feature Coming Soon',
              text: 'Bulk remove functionality will be available soon',
              icon: 'info',
              confirmButtonColor: '#c9a96e'
          });
      }
  }

  // Plan trip
  function planTrip(destinationId, destinationName) {
      Swal.fire({
          title: 'Plan Your Trip',
          text: `Ready to plan your trip to ${destinationName}?`,
          icon: 'question',
          showCancelButton: true,
          confirmButtonColor: '#c9a96e',
          cancelButtonColor: '#6b5b4f',
          confirmButtonText: 'Yes, let\'s go!',
          cancelButtonText: 'Not yet'
      }).then((result) => {
          if (result.isConfirmed) {
              window.location.href = `/plan-trip?destination=${destinationId}`;
          }
      });
  }