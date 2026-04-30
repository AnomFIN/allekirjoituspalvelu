import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import DocumentCard from '../components/DocumentCard';
import StatusBadge from '../components/StatusBadge';

const MOCK_DOCS = [
  {
    id: '1',
    name: 'Työsopimus_2025.pdf',
    date: '24.4.2025',
    status: 'waiting',
    signers: [
      { name: 'Matti Virtanen', email: 'matti@esimerkki.fi', signed: true },
      { name: 'Anna Korhonen', email: 'anna@esimerkki.fi', signed: false },
      { name: 'Pekka Leinonen', email: 'pekka@esimerkki.fi', signed: false },
    ],
  },
  {
    id: '2',
    name: 'Vuokrasopimus_Helsinki.pdf',
    date: '22.4.2025',
    status: 'signed',
    signers: [
      { name: 'Laura Mäkinen', email: 'laura@esimerkki.fi', signed: true },
      { name: 'Jukka Hämäläinen', email: 'jukka@esimerkki.fi', signed: true },
    ],
  },
  {
    id: '3',
    name: 'NDA_Sopimus_2025.pdf',
    date: '20.4.2025',
    status: 'rejected',
    signers: [
      { name: 'Sari Nieminen', email: 'sari@esimerkki.fi', signed: false },
    ],
  },
  {
    id: '4',
    name: 'Kauppasopimus_Q2.pdf',
    date: '18.4.2025',
    status: 'waiting',
    signers: [
      { name: 'Mikko Ojala', email: 'mikko@esimerkki.fi', signed: true },
      { name: 'Tiina Rantanen', email: 'tiina@esimerkki.fi', signed: true },
      { name: 'Ville Laukkanen', email: 'ville@esimerkki.fi', signed: false },
    ],
  },
  {
    id: '5',
    name: 'Yhteistyösopimus_2025.pdf',
    date: '15.4.2025',
    status: 'signed',
    signers: [
      { name: 'Erika Salminen', email: 'erika@esimerkki.fi', signed: true },
      { name: 'Timo Jokinen', email: 'timo@esimerkki.fi', signed: true },
      { name: 'Pirjo Heikkinen', email: 'pirjo@esimerkki.fi', signed: true },
    ],
  },
];

const stats = [
  { label: 'Odottaa allekirjoitusta', value: 2, color: 'text-amber-600', bg: 'bg-amber-50', border: 'border-amber-200', status: 'waiting' },
  { label: 'Allekirjoitettu', value: 2, color: 'text-green-700', bg: 'bg-green-50', border: 'border-green-200', status: 'signed' },
  { label: 'Hylätty', value: 1, color: 'text-red-600', bg: 'bg-red-50', border: 'border-red-200', status: 'rejected' },
];

export default function Dashboard() {
  const navigate = useNavigate();
  const [filter, setFilter] = useState('all');

  const filtered = filter === 'all' ? MOCK_DOCS : MOCK_DOCS.filter((d) => d.status === filter);

  return (
    <div className="space-y-8">
      {/* Welcome banner */}
      <div className="bg-gradient-to-r from-blue-900 to-blue-800 rounded-2xl p-6 md:p-8 text-white shadow-lg">
        <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <h1 className="text-2xl md:text-3xl font-bold tracking-tight">Hyvää päivää! 👋</h1>
            <p className="text-blue-200 mt-1 text-sm md:text-base">
              Sinulla on <span className="text-white font-semibold">2 dokumenttia</span> odottamassa allekirjoitusta.
            </p>
          </div>
          <button
            onClick={() => navigate('/upload')}
            className="inline-flex items-center gap-2 bg-white text-blue-900 px-5 py-2.5 rounded-xl font-semibold text-sm hover:bg-blue-50 transition-colors shadow-sm self-start md:self-auto"
          >
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
            </svg>
            Lataa uusi dokumentti
          </button>
        </div>
      </div>

      {/* Stats */}
      <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
        {stats.map((stat) => (
          <button
            key={stat.status}
            onClick={() => setFilter(filter === stat.status ? 'all' : stat.status)}
            className={`${stat.bg} border ${stat.border} rounded-2xl p-5 text-left hover:shadow-md transition-all ${
              filter === stat.status ? 'ring-2 ring-blue-900 ring-offset-2' : ''
            }`}
          >
            <p className="text-sm text-slate-500">{stat.label}</p>
            <p className={`text-3xl font-bold mt-1 ${stat.color}`}>{stat.value}</p>
          </button>
        ))}
      </div>

      {/* Document list */}
      <div>
        <div className="flex items-center justify-between mb-4">
          <h2 className="text-lg font-semibold text-slate-900">Dokumentit</h2>
          <div className="flex items-center gap-2">
            {['all', 'waiting', 'signed', 'rejected'].map((f) => (
              <button
                key={f}
                onClick={() => setFilter(f)}
                className={`px-3 py-1.5 rounded-lg text-xs font-medium transition-colors ${
                  filter === f
                    ? 'bg-blue-900 text-white'
                    : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'
                }`}
              >
                {f === 'all' ? 'Kaikki' : <StatusBadge status={f} />}
              </button>
            ))}
          </div>
        </div>

        {filtered.length === 0 ? (
          <div className="bg-white rounded-2xl border border-slate-200 p-12 text-center">
            <div className="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg className="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5}
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <p className="text-slate-500 text-sm">Ei dokumentteja tässä tilassa</p>
          </div>
        ) : (
          <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            {filtered.map((doc) => (
              <DocumentCard key={doc.id} doc={doc} />
            ))}
          </div>
        )}
      </div>
    </div>
  );
}
